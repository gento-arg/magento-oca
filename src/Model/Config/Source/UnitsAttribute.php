<?php

namespace Gento\Oca\Model\Config\Source;

class UnitsAttribute extends AbstractSource
{
    const UNIT_CENTIMETER = 'cm';
    const UNIT_METER = 'm';
    const UNIT_MILLIMETER = 'mm';

    /**
     * @inheridoc
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
