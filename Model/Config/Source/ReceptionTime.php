<?php

namespace Gento\Oca\Model\Config\Source;

class ReceptionTime extends AbstractSource
{
    /**
     * @inheridoc
     */
    public function toArray()
    {
        return [
            '1' => __('08:00 to 17:00'),
            '2' => __('08:00 to 12:00'),
            '3' => __('14:00 to 17:00'),
        ];
    }
}
