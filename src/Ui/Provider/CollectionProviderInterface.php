<?php

namespace Gento\Oca\Ui\Provider;

use Gento\Oca\Model\ResourceModel\AbstractCollection;

interface CollectionProviderInterface
{
    /**
     * @return AbstractCollection
     */
    public function getCollection();
}
