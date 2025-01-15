<?php

namespace Gento\Oca\Model;

use Exception;
use Gento\Oca\Api\Data\HistoryInterface;
use Gento\Oca\Api\Data\HistoryInterfaceFactory;
use Gento\Oca\Api\Data\HistorySearchResultInterface;
use Gento\Oca\Api\Data\HistorySearchResultInterfaceFactory;
use Gento\Oca\Api\HistoryRepositoryInterface;
use Gento\Oca\Model\ResourceModel\History as HistoryResourceModel;
use Gento\Oca\Model\ResourceModel\History\Collection;
use Gento\Oca\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * History resource model
     *
     * @var HistoryResourceModel
     */
    protected $resource;

    /**
     * History collection factory
     *
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * History interface factory
     *
     * @var HistoryInterfaceFactory
     */
    protected $historyInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var HistorySearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * HistoryRepository constructor.
     * @param HistoryResourceModel $resource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param HistoryInterfaceFactory $historyInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param HistorySearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        HistoryResourceModel $resource,
        HistoryCollectionFactory $historyCollectionFactory,
        HistoryInterfaceFactory $historyInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        HistorySearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->historyInterfaceFactory = $historyInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Retrieve History
     *
     * @param int $historyId
     * @return HistoryInterface
     * @throws NoSuchEntityException
     */
    public function get($historyId)
    {
        if (!isset($this->instances[$historyId])) {
            /** @var HistoryInterface|AbstractModel $history */
            $history = $this->historyInterfaceFactory->create();
            $this->resource->load($history, $historyId);
            if (!$history->getId()) {
                throw new NoSuchEntityException(__('Requested History doesn\'t exist'));
            }
            $this->instances[$historyId] = $history;
        }
        return $this->instances[$historyId];
    }

    /**
     * Retrieve Operatories matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return HistorySearchResultInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var HistorySearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->historyCollectionFactory->create();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ?
                        SortOrder::SORT_ASC :
                        SortOrder::SORT_DESC
                );
            }
        } else {
            $collection->addOrder('main_table.' . HistoryInterface::REQUEST_ID, SortOrder::SORT_ASC);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var HistoryInterface[] $histories */
        $histories = [];
        /** @var History $history */
        foreach ($collection as $history) {
            /** @var HistoryInterface $historyDataObject */
            $historyDataObject = $this->historyInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $historyDataObject,
                $history->getData(),
                HistoryInterface::class
            );
            $histories[] = $historyDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($histories);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws InputException
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }

    /**
     * clear caches instances
     * @return void
     */
    public function clear()
    {
        $this->instances = [];
    }

    /**
     * Save history.
     *
     * @param HistoryInterface $history
     * @return HistoryInterface
     * @throws CouldNotSaveException
     */
    public function save(HistoryInterface $history)
    {
        /** @var HistoryInterface|AbstractModel $history */
        try {
            $this->resource->save($history);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the history : %1',
                $exception->getMessage()
            ));
        }
        return $history;
    }

    /**
     * @return string[]
     */
    public function getServicesList(): array
    {
        $collection = $this->historyCollectionFactory->create();
        $select = $collection->getSelect()->reset()
            ->from($collection->getMainTable(), 'service')
            ->group('service');
        return $this->resource->getConnection()
            ->fetchCol($select);
    }

    /**
     * @return string[]
     */
    public function getStatusList(): array
    {
        $collection = $this->historyCollectionFactory->create();
        $select = $collection->getSelect()->reset()
            ->from($collection->getMainTable(), 'status')
            ->group('status');
        return $this->resource->getConnection()
            ->fetchCol($select);
    }

    /**
     * @inheritDoc
     */
    public function deleteHistories($dayLimit)
    {
        $collection = $this->historyCollectionFactory->create();
        $connection = $this->resource->getConnection();
        $tableName = $collection->getMainTable();

        $connection->beginTransaction();
        try {
            $deleteQuery = $connection->select()
                ->reset()
                ->from($tableName)
                ->where('created_at < DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)', $dayLimit);
            $connection->query($connection->deleteFromSelect($deleteQuery, $tableName));

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
