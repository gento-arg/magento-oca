<?php

namespace Gento\Oca\Model\ResourceModel;

class Branch extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oca_branches', 'branch_id');
    }
}
