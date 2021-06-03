<?php

namespace Gento\Oca\Model\ResourceModel;

class History extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oca_webservice_requests', 'request_id');
    }
}
