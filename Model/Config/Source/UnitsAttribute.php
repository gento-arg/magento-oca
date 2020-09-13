<?php

namespace Gento\Oca\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class UnitsAttribute implements OptionSourceInterface
{
    const UNIT_CENTIMETER = 'cm';
    const UNIT_MILLIMETER = 'mm';
    const UNIT_METER = 'm';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return array_map(function ($key, $label) {
            return ['value' => $key, 'label' => $label];
        }, array_keys($this->toArray()), $this->toArray());
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::UNIT_MILLIMETER => __('Millimeters'),
            self::UNIT_CENTIMETER => __('Centimeters'),
            self::UNIT_METER => __('Meters'),
        ];
    }
}
