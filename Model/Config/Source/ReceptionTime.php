<?php

namespace Gento\Oca\Model\Config\Source;

class ReceptionTime implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
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
            '1' => __('08:00 to 17:00'),
            '2' => __('08:00 to 12:00'),
            '3' => __('14:00 to 17:00'),
        ];
    }
}
