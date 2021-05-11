<?php

namespace Gento\Oca\Model\ResourceModel\Operatory;

use Gento\Oca\Model\Operatory;
use Gento\Oca\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Operatory::class,
            \Gento\Oca\Model\ResourceModel\Operatory::class
        );
    }

    /**
     * @return Collection
     */
    public function getActiveList()
    {
        return $this->addFieldToFilter('active', ['eq' => true]);
    }

    /**
     * @return Collection
     */
    public function getUsesIdci()
    {
        return $this->addFieldToFilter('uses_idci', ['eq' => true]);
    }
}
