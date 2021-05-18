<?php

namespace Gento\Oca\Plugin\Api;

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
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
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
                $this->quoteRepository->save($quote);
            }
        }
    }
}
