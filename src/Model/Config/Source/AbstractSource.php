<?php

namespace Gento\Oca\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractSource implements OptionSourceInterface
{
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
     * Return an key-value array with options
     * @return mixed
     */
    abstract public function toArray();
}
