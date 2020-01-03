<?php
namespace Gento\Oca\Model\ResourceModel\Branch;

use Gento\Oca\Model\Branch;
use Gento\Oca\Model\ResourceModel\AbstractCollection;

/**
 * @api
 */
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
            Branch::class,
            \Gento\Oca\Model\ResourceModel\Branch::class
        );
    }
}
