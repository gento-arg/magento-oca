<?php

namespace Gento\Oca\Model\History;

use Gento\Oca\Model\ResourceModel\AbstractCollection;
use Gento\Oca\Model\ResourceModel\History\CollectionFactory;
use Gento\Oca\Ui\Provider\CollectionProviderInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class CollectionProvider implements CollectionProviderInterface
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * CollectionRetriever constructor.
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return AbstractCollection|AbstractDb
     * @throws LocalizedException
     */
    public function getCollection()
    {
        return $this->filter->getCollection($this->collectionFactory->create());
    }
}
