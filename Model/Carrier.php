<?php

namespace Gento\Oca\Model;

use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = 'gento_oca';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Gento\Oca\Model\OperatoryFactory
     */
    protected $_operatoryCollectionFactory;

    /**
     * @param \Gento\Oca\Model\OcaApi
     */
    private $ocaApi;

    /**
     * @param \Gento\Oca\Model\BranchRepositoryFactory
     */
    private $branchRepositoryFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory $operatoryCollectionFactory,
        \Gento\Oca\Model\OcaApi $ocaApi,
        \Gento\Oca\Model\BranchRepositoryFactory $branchRepositoryFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->ocaApi = $ocaApi;
        $this->branchRepositoryFactory = $branchRepositoryFactory;
        $this->eventManager = $eventManager;
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
        if ($volume == 0) {
            $volume = $this->getConfigData("min_box_volume");
        }

        $freeBoxes = 0;
        $weight = floatval($request->getPackageWeight());

        // @todo Set configurable price by box
        $price = 5; // $this->getConfigData('price')

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getFreeShipping() && !$item->getProduct()->isVirtual()) {
                    $freeBoxes += $item->getQty();
                }
            }
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
        \Magento\Shipping\Model\Rate\Result $rateResult,
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

        if ($description) {
            $_method_title = __(
                '%1 (%2 dias)',
                // Nombre
                $description,
                // Dias
                $plazoEntrega
            );
            $method->setMethodTitle($_method_title);
        } else {
            if ($operatory->getPaysOnDestination()) {
                $methodTitle = __('%1. Pay %2 to courrier.',
                    $operatory->getName(),
                    $method->getData('price')
                    // Mage::helper('core')->currency(
                    //     $total,
                    //     true,
                    //     false
                    // )
                );
                $method->setMethodTitle($methodTitle);
            } else {
                $_method_title = __(
                    '%1 (%2 dias)',
                    // Nombre
                    $operatory->getName(),
                    // Dias
                    $plazoEntrega
                );
                $method->setMethodTitle($_method_title);
            }
        }

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

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
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
}
