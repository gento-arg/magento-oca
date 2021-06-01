<?php

namespace Gento\Oca\Model\ResourceModel\Operatory;

use Gento\Oca\Model\Config\Source\OperatoryTypes;
use Gento\Oca\Model\Operatory;
use Gento\Oca\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return Collection
     */
    public function getActiveList()
    {
        return $this->addFieldToFilter('active', ['eq' => true])
            ->setOrder('position', 'ASC');
    }

    /**
     * @return Collection
     */
    public function getDeliveryToBranch()
    {
        return $this->addFieldToFilter('operatory_type', ['in' => [
            OperatoryTypes::TYPE_BRANCH2BRANCH,
            OperatoryTypes::TYPE_DOOR2BRANCH,
        ]]);
    }

    public function getFilterByCode($operatoryCode)
    {
        return $this->addFieldToFilter('code', ['eq' => $operatoryCode]);
    }

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
}
