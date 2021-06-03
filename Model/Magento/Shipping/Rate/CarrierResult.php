<?php

namespace Gento\Oca\Model\Magento\Shipping\Rate;

use Magento\Shipping\Model\Rate\CarrierResult as CarrierResultBase;
use Magento\Shipping\Model\Rate\Result;

class CarrierResult extends CarrierResultBase
{
    /**
     * @var Result[][]
     */
    protected $resultados = [];

    /**
     * Append result received from a carrier.
     *
     * @param Result $result
     * @param bool $appendFailed Append result's errors as well.
     * @return void
     */
    public function appendResult(Result $result, bool $appendFailed): void
    {
        $this->resultados[] = ['result' => $result, 'appendFailed' => $appendFailed];
    }

    /**
     * @inheritDoc
     */
    public function getAllRates()
    {
        while ($resultData = array_shift($this->resultados)) {
            if ($resultData['result']->getError()) {
                if ($resultData['appendFailed']) {
                    $this->append($resultData['result']);
                    $needsSorting = true;
                }
            } else {
                $this->append($resultData['result']);
            }
        }
        return $this->_rates;
    }
}
