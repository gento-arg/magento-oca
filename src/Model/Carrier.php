<?php

namespace Gento\Oca\Model;

use DateTime;
use Exception;
use Gento\Oca\Helper\Data;
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
use Magento\Framework\Event\ManagerInterface;
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
    const XML_PATH_FRANJAHORARIA = 'carriers/gento_oca/reception_time';
    /**
     * @var string
     */
    protected $_code = 'gento_oca';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var OperatoryFactory
     */
    protected $_operatoryCollectionFactory;

    /**
     * @param OcaApi
     */
    private $ocaApi;

    /**
     * @param ManagerInterface
     */
    private $eventManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Carrier constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param RateResultErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param RateFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CollectionFactory $operatoryCollectionFactory
     * @param OcaApi $ocaApi
     * @param ManagerInterface $eventManager
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
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig,
        RateResultErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CollectionFactory $operatoryCollectionFactory,
        OcaApi $ocaApi,
        ManagerInterface $eventManager,
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
        PricingHelperData $pricingHelper,
        Data $helper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->_operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->ocaApi = $ocaApi;
        $this->eventManager = $eventManager;
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
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
    public function getAllowedMethods()
    {
        return [
            $this->_code => $this->getConfigData('title'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $cps = trim($this->getConfigData('disabled_cp'));
        if ($cps) {
            $cp = $request->getDestPostcode();
            $cps = explode("\n", $cps);

            if (in_array($cp, $cps)) {
                return false;
            }
        }

        $rateResult = $this->_rateFactory->create();

        $volume = 0;
        $freeBoxes = 0;
        $weight = floatval($request->getPackageWeight());

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getFreeShipping() && !$item->getProduct()->isVirtual()) {
                    $freeBoxes += $item->getQty();
                }

                if (!$item->getProduct()->isVirtual()) {
                    $volume += $this->calculateVolume($item->getProduct());
                }
            }
        }

        if ($volume == 0) {
            $volume = $this->getConfigData("volume/min");
        }

        $operatory = $this->_operatoryCollectionFactory->create();

        $senderZipCode = $request->getPostcode();
        $packageValue = $request->getPackageValue();

        $receiverZipcode = $request->getDestPostcode();

        // @todo Create a method to calculate package qty
        $packageQty = 1;

        foreach ($operatory->getActiveList() as $operatory) {
            $this->processOperatory(
                $operatory,
                $rateResult,
                $weight,
                $volume,
                $senderZipCode,
                $receiverZipcode,
                $packageValue,
                $packageQty,
                $request->getPackageQty(),
                $freeBoxes
            );
        }

        return $rateResult;
    }

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
        $freeQty
    ) {
        $tarifa = null;
        $errorMessage = '';
        try {
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
            if ($this->getConfigData('showmethod')) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title') . ' - ' . $operatory->getName());
                $errorMessage = $this->getConfigData('specificerrmsg') ?: $errorMessage;
                $error->setErrorMessage($errorMessage);
                $rateResult->append($error);
            }
            return $rateResult;
        }

        $quoteValue = $tarifa->Total;

        if ($itemQty <= $freeQty) {
            $quoteValue = 0;
        }

        $plazoEntrega = $tarifa->PlazoEntrega + (int)$this->_scopeConfig->getValue('carriers/gento_oca/days_extra');
        $this->_addRate(
            $rateResult,
            $operatory,
            $operatory->getCode(),
            $quoteValue,
            $plazoEntrega
        );

        return $rateResult;
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
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getContainerTypes(DataObject $params = null): array
    {
        return [
            'gento_oca' => $this->getConfigData('title')
        ];
    }

    protected function calculateVolume(Product $product)
    {
        /** @var Product $product */
        $product = $this->productRepository->getById($product->getId());

        list($width, $height, $length) = $this->helper->getProductSize($product);

        return $width * $height * $length;
    }

    protected function _addRate(
        $rateResult,
        $operatory,
        $operatoryCode,
        $total = 0,
        $plazoEntrega,
        $description = false
    ) {
        $shouldAddTax = $this->getStoreConfig('tax/calculation/shipping_includes_tax');
        $shouldShowDays = $this->getConfigData('show_days');
        if ($shouldAddTax) {
            // @todo use custom shipping tax configurable on backend
            $total = 1.21 * floatval($total);
        }

        /** @var Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
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
    protected function _doShipmentRequest(DataObject $request)
    {
        $result = new DataObject();
        try {
            /**
             * WIP
             * ✅ Domicilio a Domicilio
             * ✅ Domicilio a Sucursal
             *
             * ❌ Sucursal a Sucursal
             * ❌ Sucursal a Domicilio
             */

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
            $request->setFranjaHoraria($this->getStoreConfig(self::XML_PATH_FRANJAHORARIA));

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
            $code = $this->getConfigData('code');
            $title = $this->getConfigData('title');
            $url = $this->getConfigData('tracking_url');
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
