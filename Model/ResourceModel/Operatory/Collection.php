<?php

namespace Gento\Oca\Model\ResourceModel\Operatory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Gento\Oca\Model\Operatory', 'Gento\Oca\Model\ResourceModel\Operatory');
    }

    public function getActiveList()
    {
        return $this->addFieldToFilter('active', ['eq' => 1]);
    }

}
