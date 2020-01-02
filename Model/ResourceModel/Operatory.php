<?php

namespace Gento\Oca\Model\ResourceModel;

class Operatory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oca_operatories', 'operatory_id');
    }
}
