<?php
namespace Gento\Oca\Model;

use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Api\Data\OperatoryInterfaceFactory;
use Gento\Oca\Api\Data\OperatorySearchResultInterfaceFactory;
use Gento\Oca\Api\OperatoryRepositoryInterface;
use Gento\Oca\Model\ResourceModel\Operatory as OperatoryResourceModel;
use Gento\Oca\Model\ResourceModel\Operatory\Collection;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory as OperatoryCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;

class OperatoryRepository implements OperatoryRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Operatory resource model
     *
     * @var OperatoryResourceModel
     */
    protected $resource;

    /**
     * Operatory collection factory
     *
     * @var OperatoryCollectionFactory
     */
    protected $operatoryCollectionFactory;

    /**
     * Operatory interface factory
     *
     * @var OperatoryInterfaceFactory
     */
    protected $operatoryInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var OperatorySearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * @param OperatoryResourceModel $resource
     * @param OperatoryCollectionFactory $operatoryCollectionFactory
     * @param OperatorynterfaceFactory $operatoryInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param OperatorySearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        OperatoryResourceModel $resource,
        OperatoryCollectionFactory $operatoryCollectionFactory,
        OperatoryInterfaceFactory $operatoryInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        OperatorySearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->operatoryInterfaceFactory = $operatoryInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save Operatory.
     *
     * @param \Gento\Oca\Api\Data\OperatoryInterface $operatory
     * @return \Gento\Oca\Api\Data\OperatoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(OperatoryInterface $operatory)
    {
        /** @var OperatoryInterface|\Magento\Framework\Model\AbstractModel $operatory */
        try {
            $this->resource->save($operatory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Operatory: %1',
                $exception->getMessage()
            ));
        }
        return $operatory;
    }

    /**
     * Retrieve Operatory
     *
     * @param int $operatoryId
     * @return \Gento\Oca\Api\Data\OperatoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($operatoryId)
    {
        if (!isset($this->instances[$operatoryId])) {
            /** @var OperatoryInterface|\Magento\Framework\Model\AbstractModel $operatory */
            $operatory = $this->operatoryInterfaceFactory->create();
            $this->resource->load($operatory, $operatoryId);
            if (!$operatory->getId()) {
                throw new NoSuchEntityException(__('Requested Operatory doesn\'t exist'));
            }
            $this->instances[$operatoryId] = $operatory;
        }
        return $this->instances[$operatoryId];
    }

    /**
     * Retrieve Operatories matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Gento\Oca\Api\Data\OperatorySearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Gento\Oca\Api\Data\OperatorySearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Gento\Oca\Model\ResourceModel\Operatory\Collection $collection */
        $collection = $this->operatoryCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
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
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
                );
            }
        } else {
            $collection->addOrder('main_table.' . OperatoryInterface::OPERATORY_ID, SortOrder::SORT_ASC);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var OperatoryInterface[] $operatories */
        $operatories = [];
        /** @var \Gento\Oca\Model\Operatory $operatory */
        foreach ($collection as $operatory) {
            /** @var OperatoryInterface $operatoryDataObject */
            $operatoryDataObject = $this->operatoryInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $operatoryDataObject,
                $operatory->getData(),
                OperatoryInterface::class
            );
            $operatories[] = $operatoryDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($operatories);
    }

    /**
     * Delete Operatory
     *
     * @param OperatoryInterface $operatory
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(OperatoryInterface $operatory)
    {
        /** @var OperatoryInterface|\Magento\Framework\Model\AbstractModel $operatory */
        $id = $operatory->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($operatory);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to removeOperatory %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Operatory by ID.
     *
     * @param int $operatoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($operatoryId)
    {
        $operatory = $this->get($operatoryId);
        return $this->delete($operatory);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
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
}
