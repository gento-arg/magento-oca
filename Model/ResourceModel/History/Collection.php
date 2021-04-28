<?php

namespace Gento\Oca\Model\ResourceModel\History;

use Gento\Oca\Model\History;
use Gento\Oca\Model\ResourceModel\AbstractCollection;
use Gento\Oca\Model\ResourceModel\History as HistoryResourceModel;

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
            History::class,
            HistoryResourceModel::class
        );
    }
}
