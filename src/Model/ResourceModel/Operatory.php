<?php

namespace Gento\Oca\Model\ResourceModel;

class Operatory extends AbstractModel
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
