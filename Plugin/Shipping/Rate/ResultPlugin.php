<?php

namespace Gento\Oca\Plugin\Shipping\Rate;

use Closure;
use Magento\Shipping\Model\Rate\Result;

class ResultPlugin
{

    /**
     * @param Address $subject
     * @param Closure $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSortRatesByPrice(
        Result $subject,
        Closure $proceed
    ) {
        foreach ($subject->getAllRates() as $rate) {
            if ($rate->getCarrier() === 'gento_oca') {
                return $subject;
            }
        }
        return $proceed();
    }
}
