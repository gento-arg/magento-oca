<?php

namespace Gento\Oca\Plugin\Api;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

class ShippingInformationManagementPlugin
{
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $quoteId,
        ShippingInformationInterface $addressInformation
    ) {

    }
}