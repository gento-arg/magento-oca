<?php

namespace Gento\Oca\Plugin\Api\Quote;

use Closure;
use Magento\Quote\Model\Quote\Address;

class AddressPlugin
{

    /**
     * @param Address $subject
     * @param Closure $proceed
     */
    public function aroundGetAllShippingRates(
        Address $subject,
        Closure $proceed
    ) {
        $rates = $proceed();

        foreach ($rates as $rate) {
            if ($rate->getCarrier() === 'gento_oca' &&
                $rate->getAddress()->getExtensionAttributes()->getGentoOcaBranchDescription()
            ) {
                $branchDescription = $rate->getAddress()->getExtensionAttributes()->getGentoOcaBranchDescription();
                $branchDescription = $rate->getMethodTitle() . ' ' . $branchDescription;
                $rate->setMethodTitle($branchDescription);
//                $rate->setMethodDescription($branchDescription);
            }
        }

        return $rates;
    }

}