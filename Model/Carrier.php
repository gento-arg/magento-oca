<?php

namespace Gento\Oca\Model;

use Gento\Oca\Model\BranchRepositoryFactory;
use Gento\Oca\Model\Config\Source\UnitsAttribute;
use Gento\Oca\Model\OcaApi;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateResultErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as ResultErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
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
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var OperatoryFactory
     */
    protected $_operatoryCollectionFactory;

    /**
     * @param OcaApi
     */
    private $ocaApi;

    /**
     * @param BranchRepositoryFactory
     */
    private $branchRepositoryFactory;

    /**
     * @param ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig,
        RateResultErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CollectionFactory $operatoryCollectionFactory,
        OcaApi $ocaApi,
        BranchRepositoryFactory $branchRepositoryFactory,
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
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->ocaApi = $ocaApi;
        $this->branchRepositoryFactory = $branchRepositoryFactory;
        $this->eventManager = $eventManager;
        $this->pricingHelper = $pricingHelper;
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

    public function getAllowedMethods()
    {
        return [
            $this->_code => $this->getConfigData('title'),
        ];
    }

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

        $rateResult = $this->_rateResultFactory->create();

        $volume = 0;
        $freeBoxes = 0;
        $weight = floatval($request->getPackageWeight());

        // @todo Set configurable price by box
        $price = 5; // $this->getConfigData('price')

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

        $shippingPrice = ($request->getPackageQty() * $price) - ($freeBoxes * $price);
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        $operatory = $this->_operatoryCollectionFactory->create();

        $senderZipCode = $request->getPostcode();
        $packageValue = $request->getPackageValue();

        $receiverZipcode = $request->getDestPostcode();

        // @todo Create a method to calculate package qty
        $packageQty = 1;

        if ($shippingPrice !== false) {
            foreach ($operatory->getActiveList() as $operatory) {
                $this->processOperatory(
                    $operatory,
                    $rateResult,
                    $weight,
                    $volume,
                    $senderZipCode,
                    $receiverZipcode,
                    $packageValue,
                    $packageQty
                );
            }
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
        $packageQty
    ) {
        $tarifa = $this->ocaApi->getQuote(
            $operatory->getCode(),
            $weight,
            $volume,
            $senderZipcode,
            $receiverZipcode,
            $packageQty,
            $packageValue
        );

        if ($tarifa == null) {
            return;
        }

        $quoteValue = $tarifa->Total;

        $plazoEntrega = $tarifa->PlazoEntrega + (int) $this->_scopeConfig->getValue('carriers/gento_oca/days_extra');
        if ($operatory->getUsesIdci()) {
            $branches = $this->ocaApi->getBranchesZipCode($operatory->getCode(), $receiverZipcode);

            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $branches,
                'operatory' => $operatory->getCode(),
            ]);

            /**
             * @var \Gento\Oca\Model\BranchRepository $branchRepository
             */
            $branchRepository = $this->branchRepositoryFactory->create();
            foreach ($branches as $branchData) {
                /**
                 * @var \Gento\Oca\Model\Branch $branch
                 */
                $branch = $branchRepository->getByCode($branchData['code']);
                if (!$branch->getActive()) {
                    continue;
                }

                $code = $operatory->getCode() . "_" . $branch->getCode();
                $description = $operatory->getName() . " " . $branch->getFullDescription();

                $this->_addRate(
                    $rateResult,
                    $operatory,
                    $code,
                    $quoteValue,
                    $plazoEntrega,
                    $description
                );
            }
        } else {
            $this->_addRate(
                $rateResult,
                $operatory,
                $operatory->getCode(),
                $quoteValue,
                $plazoEntrega
            );
        }

        return $rateResult;
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
        if ($shouldAddTax) {
            // @todo use custom shipping tax configurable on backend
            $total = 1.21 * floatval($total);
        }

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
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

        if ($operatory->getPaysOnDestination()) {
            $methodTitle = __(
                '%1. (%2 dias). Pagar %3 en destino.',
                $description,
                // Dias
                $plazoEntrega,
                $this->pricingHelper->currency($total, true, false)
            );
        } else {
            $methodTitle = __(
                '%1 (%2 dias)',
                // Nombre
                $description,
                // Dias
                $plazoEntrega
            );
        }
        $method->setMethodTitle($methodTitle);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $rateResult->append($method);
    }

    /**
     * @inheritdoc
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    protected function getStoreConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }

    protected function _doShipmentRequest(DataObject $request)
    {
        return $request;
    }

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
            $url = 'https://www1.oca.com.ar/ocaepakNet/Views/ConsultaTracking/TrackingConsult.aspx?numberTracking=';
            $trackingResults = $this->ocaApi->getTracking($tracking);
            /** @var \Magento\Shipping\Model\Tracking\Result\Status $status */
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($code);
            $status->setCarrierTitle($title);
            $status->setTracking($tracking);
            $status->setUrl($url . $tracking);
            $progress = [];
            foreach ($trackingResults as $trackingResult) {
                $fecha = new \DateTime($trackingResult['Fecha']);

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

    protected function calculateVolume(Product $product)
    {
        $attrs = ['width', 'height', 'length'];

        foreach ($attrs as $att) {
            ${$att . 'Att'} = $this->getConfigData("volume/" . $att);
        }

        if (!$widthAtt || !$heightAtt || !$lengthAtt) {
            return 0;
        }

        /** @var Product $product */
        $product = $this->productRepository->getById($product->getId());

        $unitValue = $this->getConfigData("volume/unit");
        $factor = 1;
        if ($unitValue == UnitsAttribute::UNIT_CENTIMETER) {
            $factor = 100;
        } elseif ($unitValue == UnitsAttribute::UNIT_MILLIMETER) {
            $factor = 1000;
        }
        foreach ($attrs as $att) {
            ${$att} = (float) $product->getData(${$att . 'Att'});
            if (${$att} <= 0) {
                return 0;
            }
            ${$att} /= $factor;
        }

        $volume = $width * $height * $length;
        if ($volume == 0) {
            return $volume;
        }

        return $volume;
    }
}
