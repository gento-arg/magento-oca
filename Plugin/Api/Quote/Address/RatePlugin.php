<?php

namespace Gento\Oca\Plugin\Api\Quote\Address;

use Closure;
use Magento\Quote\Model\Quote\Address\Rate;

class RatePlugin
{
    public function aroundGetMethodTitle(
        Rate $subject,
        Closure $proceed
    ) {
        $methodTitle = $proceed();
        if ($subject->getCarrier() !== 'gento_oca' ||
            !$subject->getAddress()->getExtensionAttributes()->getGentoOcaBranchDescription()
        ) {
            return $methodTitle;
        }

        $branchDescription = $subject->getAddress()->getExtensionAttributes()->getGentoOcaBranchDescription();

        return $methodTitle . ' ' . $branchDescription;
    }
}