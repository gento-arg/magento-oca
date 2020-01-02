<?php

namespace Gento\Oca\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Carrier extends AbstractCarrier implements CarrierInterface
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
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Gento\Oca\Model\ResourceModel\OperatoryFactory $operatoryCollectionFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->_objectFactory = $objectFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
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

        $cuit = $this->getStoreConfig('tax/defaults/cuit');

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
                    $cuit,
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
        $cuit,
        $senderZipcode,
        $receiverZipcode,
        $packageValue,
        $packageQty
    ) {
        // If operatory use id centro imposicion (Branch)
        if ($operatory->getUsesIdci()) {
            $branches = $operatory->getBranches();

            foreach ($branches as $branch) {
                // $code = $operatory->getCode() . "_" . $branch->getCode();
                $code = $branch->getFullCode();
                // $description = $operatory->getName() . " " . $branch->getFullDescription();
                $description = $branch->getFullDescription();

                $this->_addRate(
                    $rateResult,
                    $operatory,
                    $weight,
                    $volume,
                    $senderZipcode,
                    $branch->getZipcode(),
                    $packageQty,
                    $cuit,
                    $code,
                    $packageValue,
                    $description
                );
            }
        } else {
            $this->_addRate(
                $rateResult,
                $operatory,
                $weight,
                $volume,
                $senderZipcode,
                $receiverZipcode,
                1,
                $cuit,
                $operatory->getCode(),
                $packageValue
            );
        }

        return $rateResult;
    }

    protected function _addRate(
        $rateResult,
        $operatory,
        $weight,
        $volume,
        $senderZipcode,
        $receiverZipcode,
        $quantityOfPackages,
        $cuit,
        $operatoryCode,
        $subtotal = 0,
        $freeBoxes,
        $description = false
    ) {
        $calculator = $operatory->getCalculator();

        $param = $this->_objectFactory->create();
        $param->setWeight($weight);
        $param->setVolume($volume);
        $param->setSenderZipcode($senderZipcode);
        $param->setReceiverZipcode($receiverZipcode);
        $param->setQuantityOfPackages($quantityOfPackages);
        $param->setCuit($cuit);
        $param->setOperatoryCode($operatory->getCode());

        $total = $calculator->calculate($param);

        if ($total) {
            if ($operatory->getHasSecure()) {
                $total += $subtotal * $this->getConfigData('insurance_percentage') / 100;
            }

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

            $_freeShipping = false;
            if ($this->getRequest()->getFreeShipping() === true ||
                $this->getRequest()->getPackageQty() == $freeBoxes
            ) {
                $_freeShipping = true;
                $shippingPrice = 0.0;
            } elseif ($operatory->getPaysOnDestination()) {
                $shippingPrice = 0.0;
            }

            // if (!$_freeShipping) {
            //     $method->setRealPrice(round((string) $total, 2));
            // } else {
            //     $method->setRealPrice($shippingPrice);
            // }

            if ($description) {
                $method->setMethodTitle($description);
            } else {
                if ($operatory->getPaysOnDestination()) {
                    $methodTitle = __('%s. Pay %s to courrier.',
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
                    $method->setMethodTitle($operatory->getName());
                }
            }

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $rateResult->append($method);
        }
    }

    protected function getStoreConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }
}
