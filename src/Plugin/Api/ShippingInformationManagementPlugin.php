<?php

namespace Gento\Oca\Plugin\Api;

use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;

class ShippingInformationManagementPlugin
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var CollectionFactory
     */
    private $operatoryCollectionFactory;

    /**
     * @param QuoteRepository   $quoteRepository
     * @param CollectionFactory $operatoryCollectionFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        CollectionFactory $operatoryCollectionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->operatoryCollectionFactory = $operatoryCollectionFactory;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param                               $cartId
     * @param ShippingInformationInterface  $addressInformation
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($addressInformation->getShippingCarrierCode() === 'gento_oca') {
            $extAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
            $ocaBranch = $extAttributes->getGentoOcaBranch();
            if ($ocaBranch) {
                $quote = $this->quoteRepository->getActive($cartId);

                $quote->setShippingBranch($ocaBranch);
            }

            $originBranch = null;
            $operatory = $this->operatoryCollectionFactory->create()
                ->getActiveList()
                ->getFilterByCode($addressInformation->getShippingMethodCode())
                ->getFirstItem();

            if ($operatory->getId() > 0) {
                $originBranch = $operatory->getOriginBranchId();
            }
            $quote->setShippingOriginBranch($originBranch);
            $this->quoteRepository->save($quote);;
        }
    }
}
