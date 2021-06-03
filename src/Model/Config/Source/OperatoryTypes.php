<?php

namespace Gento\Oca\Model\Config\Source;

class OperatoryTypes extends AbstractSource
{
    const TYPE_BRANCH2BRANCH = 'branch2branch';
    const TYPE_BRANCH2DOOR = 'branch2door';
    const TYPE_DOOR2BRANCH = 'door2branch';
    const TYPE_DOOR2DOOR = 'door2door';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::TYPE_BRANCH2BRANCH => __('Branch to branch'),
            self::TYPE_BRANCH2DOOR => __('Branch to door'),
            self::TYPE_DOOR2BRANCH => __('Door to branch'),
            self::TYPE_DOOR2DOOR => __('Door to door'),
        ];
    }
}
