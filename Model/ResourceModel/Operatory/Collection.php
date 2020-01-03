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

    public function getActiveList()
    {
        return $this->addFieldToFilter('active', ['eq' => true]);
    }

}
