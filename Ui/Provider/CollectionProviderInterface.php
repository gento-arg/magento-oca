<?php
namespace Gento\Oca\Ui\Provider;

interface CollectionProviderInterface
{
    /**
     * @return \Gento\Oca\Model\ResourceModel\AbstractCollection
     */
    public function getCollection();
}
