<?php

declare(strict_types = 1);

namespace Gento\Oca\Model;

use DateTime;
use Exception;
use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Model\Carrier\Command\GetFreePackages;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateResultErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as ResultErrorFactory;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'gento_oca';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RateResultErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param RateFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param ResultFactory $trackFactory
     * @param ResultErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param DirectoryData $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param PricingHelperData $pricingHelper
     * @param OcaApi $ocaApi
     * @param CollectionFactory $operatoryCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigInterface $config
     * @param GetFreePackages $getFreePackages
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateResultErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        ResultFactory $trackFactory,
        ResultErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        DirectoryData $directoryData,
        StockRegistryInterface $stockRegistry,
        readonly private PricingHelperData $pricingHelper,
        readonly private OcaApi $ocaApi,
        readonly private CollectionFactory $operatoryCollectionFactory,
        readonly private ProductRepositoryInterface $productRepository,
        readonly private ConfigInterface $config,
        readonly private GetFreePackages $getFreePackages,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateResultFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag(ConfigInterface::XPATH_ACTIVE)) {
            return false;
        }

        $destPostCode = $this->ocaApi->filterPostCode($request->getDestPostcode());
        if ($destPostCode === null) {
            return false;
        }
        $cps = trim($this->getConfigData(ConfigInterface::XPATH_DISABLED_CP) ?? '');
        if ($cps) {
            $cp = $destPostCode;
            $cps = explode("\n", $cps);

            if (in_array($cp, $cps)) {
                return false;
            }
        }

        $rateResult = $this->_rateFactory->create();

        $volume = 0;
        $freeBoxes = $this->getFreePackages->execute($request);
        $weight = $request->getPackageWeight();

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if (!$item->getProduct()->isVirtual()) {
                    $volume += $this->calculateVolume($item->getProduct());
                }
            }
        }

        if ($volume == 0) {
            $volume = $this->getConfigData(ConfigInterface::XPATH_VOLUME_MIN);
        }

        $operatory = $this->operatoryCollectionFactory->create();

        $senderZipCode = $request->getPostcode();
        $packageValue = $request->getPackageValue();

        // @todo Create a method to calculate package qty
        $packageQty = 1;

        $maxValuePackage = 0;
        if ($this->getConfigFlag(ConfigInterface::XPATH_ENABLED_MAX_VALUE_PACKAGE)) {
            $maxValuePackage = (int)$this->getConfigData(ConfigInterface::XPATH_MAX_VALUE_PACKAGE);
        }

        foreach ($operatory->getActiveList() as $operatory) {
            $this->processOperatory(
                $operatory,
                $rateResult,
                $weight,
                $volume,
                $senderZipCode,
                $destPostCode,
                $packageValue,
                $packageQty,
                $request->getPackageQty(),
                $freeBoxes,
                $maxValuePackage
            );
        }

        return $rateResult;
    }

    protected function calculateVolume(Product $product)
    {
        /** @var Product $product */
        $product = $this->productRepository->getById($product->getId());

        [$width, $height, $length] = $this->config->getProductSize($product);

        return $width * $height * $length;
    }

    /**
     * @param $operatory
     * @param Result $rateResult
     * @param $weight
     * @param $volume
     * @param $senderZipcode
     * @param $receiverZipcode
     * @param $packageValue
     * @param $packageQty
     * @param $itemQty
     * @param $freeQty
     * @param int $maxValuePackage
     * @return Result
     */
    public function processOperatory(
        $operatory,
        Result $rateResult,
        $weight,
        $volume,
        $senderZipcode,
        $receiverZipcode,
        $packageValue,
        $packageQty,
        $itemQty,
        $freeQty,
        int $maxValuePackage
    ) {
        $tarifa = null;
        $errorMessage = '';
        try {
            if ($maxValuePackage > 0 && $packageValue > $maxValuePackage) {
                $amount = $this->_currencyFactory->create();
                throw new LocalizedException(__(
                    'OCA only insures shipments up to %1. Orders above this amount cannot be processed with this shipping method.',
                    $amount->formatTxt($maxValuePackage)
                ));
            }
            $tarifa = $this->ocaApi->getQuote(
                $operatory->getCode(),
                $weight,
                $volume,
                $senderZipcode,
                $receiverZipcode,
                $packageQty,
                $packageValue
            );
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        if ($tarifa == null) {
            if ($this->getConfigData(ConfigInterface::XPATH_SHOWMETHOD)) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle(
                    $this->getConfigData(ConfigInterface::XPATH_TITLE) . ' - ' . $operatory->getName()
                );
                $errorMessage = $this->getConfigData(ConfigInterface::XPATH_SPECIFIC_ERROR_MESSAGE) ?: $errorMessage;
                $error->setErrorMessage($errorMessage);
                $rateResult->append($error);
            }
            return $rateResult;
        }

        $quoteValue = $tarifa->Total;

        if ($itemQty <= $freeQty) {
            $quoteValue = 0;
        }

        $plazoEntrega = $tarifa->PlazoEntrega + $this->config->getDaysExtra();
        $this->_addRate(
            $rateResult,
            $operatory,
            $operatory->getCode(),
            $plazoEntrega,
            $quoteValue
        );

        return $rateResult;
    }

    protected function _addRate(
        $rateResult,
        $operatory,
        $operatoryCode,
        $plazoEntrega,
        $total = 0,
        $description = false
    ) {
        $shouldAddTax = $this->getStoreConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX);
        $shouldShowDays = $this->getConfigData(ConfigInterface::XPATH_SHOW_DAYS);
        if ($shouldAddTax) {
            // @todo use custom shipping tax configurable on backend
            $total = 1.21 * floatval($total);
        }

        /** @var Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData(ConfigInterface::XPATH_TITLE));
        $method->setMethod($operatoryCode);

        $shippingPrice = $total;

        if ($operatory->getPaysOnDestination()) {
            $shippingPrice = 0.0;
        }

        if (!$description) {
            $description = $operatory->getName();
        }

        $plazoEntregaString = '';
        if ($shouldShowDays && $plazoEntrega) {
            $plazoEntregaString = __('(Despacho en %1 dias)', $plazoEntrega);
        }
        $payOnDestinationString = '';
        if ($operatory->getPaysOnDestination()) {
            $payOnDestinationString = __('Pagar %1 en destino.',
                $this->pricingHelper->currency($total, true, false)
            );
        }
        $methodTitle = sprintf('%s%s%s',
            // Nombre
            $description,
            // Dias
            $plazoEntregaString,
            // Pago en destino
            $payOnDestinationString
        );
        $method->setMethodTitle($methodTitle);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $rateResult->append($method);
    }

    protected function getStoreConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }

    /**
     * @inheritdoc
     */
    public function getAllowedMethods()
    {
        return [
            $this->_code => $this->getConfigData(ConfigInterface::XPATH_TITLE),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContainerTypes(DataObject $params = null): array
    {
        return [
            'gento_oca' => $this->getConfigData(ConfigInterface::XPATH_TITLE)
        ];
    }

    /**
     * @inheritdoc
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $result = new DataObject();
        try {
            $shipperProvinceCode = $request->getData('shipper_address_state_or_province_code');
            $shipperCountryCode = $request->getData('shipper_address_country_code');
            $shipperProvince = $this->_regionFactory->create()
                ->loadByCode($shipperProvinceCode, $shipperCountryCode);
            $request->setShipperAddressProvince($shipperProvince->getName() ?
                $shipperProvince->getName() : $shipperProvinceCode);

            $recipientProvinceCode = $request->getData('recipient_address_state_or_province_code');
            $recipientCountryCode = $request->getData('recipient_address_country_code');
            $recipientProvince = $this->_regionFactory->create()
                ->loadByCode($recipientProvinceCode, $recipientCountryCode);
            $request->setRecipientAddressProvince($recipientProvince->getName() ?
                $recipientProvince->getName() : $recipientProvinceCode);
            $request->setFranjaHoraria($this->config->getReceptionTime());

            $metodo = explode('_', $request->getShippingMethod());
            $operativa = $metodo[0];
            $centroImposicion = '0';
            if ($request->getOrderShipment()->getOrder()->getShippingBranch()) {
                $centroImposicion = $request->getOrderShipment()->getOrder()->getShippingBranch();
            }
            $request->setOperativa($operativa);
            $request->setCentroImposicion($centroImposicion);

            $orderShipment = $request->getOrderShipment();
            $order = $orderShipment->getOrder();
            $originBranch = '0';
            if ($order->getShippingOriginBranch()) {
                $originBranch = $order->getShippingOriginBranch();
            }
            $request->setCentroImposicionOrigen($originBranch);

            $fields = ['street', 'number', 'floor', 'dept'];
            $shippingAddress = $order->getShippingAddress();
            foreach ($fields as $field) {
                $fieldData = $this->getConfigData('customer_address/' . $field);
                if (preg_match('/^\_\_street\_line\_([0-9]+)/', $fieldData ?? '', $matches)) {
                    $value = $shippingAddress->getStreetLine($matches[1]);
                } else {
                    $value = $shippingAddress->getData($fieldData);
                }
                $request->{'setRecipientAddress' . ucfirst($field)}($value);
            }

            $admision = $this->ocaApi->requestShipment($request);

            $data = $admision['data'];
            if (!isset($data[0])) {
                throw new LocalizedException(__('Webservice doesnt response any data'));
            }
            $ordenRetiro = $data[0]['OrdenRetiro'];
            $numeroEnvio = $data[0]['NumeroEnvio'];
            $labelContent = $this->ocaApi->getPDFEtiqueta($ordenRetiro, $numeroEnvio);

            $result->setTrackingNumber($numeroEnvio);
            $result->setShippingLabelContent(base64_decode($labelContent));
        } catch (Exception $e) {
            throw new LocalizedException(__('Error on OCA Webservice: %1', $e->getMessage()));
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $tracking) {
            /** @var \Magento\Shipping\Model\Tracking\Result $result */
            $result = $this->_trackFactory->create();
            $code = $this->getConfigData(ConfigInterface::XPATH_CODE);
            $title = $this->getConfigData(ConfigInterface::XPATH_TITLE);
            $url = $this->getConfigData(ConfigInterface::XPATH_TRACKING_URL);
            $trackingResults = $this->ocaApi->getTracking($tracking);
            /** @var Status $status */
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($code);
            $status->setCarrierTitle($title);
            $status->setTracking($tracking);
            $status->setUrl($url . $tracking);
            $progress = [];
            foreach ($trackingResults as $trackingResult) {
                $fecha = new DateTime($trackingResult['Fecha']);

                $progress[] = [
                    'deliverylocation' => $trackingResult['Sucursal'],
                    'deliverydate' => $fecha->format('d-m-Y'),
                    'deliverytime' => $fecha->format('H:n'),
                    'activity' => $trackingResult['Estado'],
                ];
            }
            $status->setProgressdetail($progress);

            $result->append($status);
        }
        return $result;
    }
}
