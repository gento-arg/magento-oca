<?php
namespace Gento\Oca\Model;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Api\Data\BranchInterfaceFactory;
use Gento\Oca\Api\Data\BranchSearchResultInterfaceFactory;
use Gento\Oca\Model\ResourceModel\Branch as BranchResourceModel;
use Gento\Oca\Model\ResourceModel\Branch\Collection;
use Gento\Oca\Model\ResourceModel\Branch\CollectionFactory as BranchCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;

class BranchRepository implements BranchRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Branch resource model
     *
     * @var BranchResourceModel
     */
    protected $resource;

    /**
     * Branch collection factory
     *
     * @var BranchCollectionFactory
     */
    protected $branchCollectionFactory;

    /**
     * Branch interface factory
     *
     * @var BranchInterfaceFactory
     */
    protected $branchInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var BranchSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * @param BranchResourceModel $resource
     * @param BranchCollectionFactory $branchCollectionFactory
     * @param BranchnterfaceFactory $branchInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param BranchSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        BranchResourceModel $resource,
        BranchCollectionFactory $branchCollectionFactory,
        BranchInterfaceFactory $branchInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        BranchSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->branchCollectionFactory = $branchCollectionFactory;
        $this->branchInterfaceFactory = $branchInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save Branch.
     *
     * @param \Gento\Oca\Api\Data\BranchInterface $branch
     * @return \Gento\Oca\Api\Data\BranchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(BranchInterface $branch)
    {
        /** @var BranchInterface|\Magento\Framework\Model\AbstractModel $branch */
        try {
            $this->resource->save($branch);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Branch: %1',
                $exception->getMessage()
            ));
        }
        return $branch;
    }

    /**
     * Retrieve Branch
     *
     * @param int $branchId
     * @return \Gento\Oca\Api\Data\BranchInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($branchId)
    {
        if (!isset($this->instances[$branchId])) {
            /** @var BranchInterface|\Magento\Framework\Model\AbstractModel $branch */
            $branch = $this->branchInterfaceFactory->create();
            $this->resource->load($branch, $branchId);
            if (!$branch->getId()) {
                throw new NoSuchEntityException(__('Requested Branch doesn\'t exist'));
            }
            $this->instances[$branchId] = $branch;
        }
        return $this->instances[$branchId];
    }

    public function getByCode($idCi)
    {
        if (!isset($this->instances['ext' . $idCi])) {
            /** @var BranchInterface|\Magento\Framework\Model\AbstractModel $branch */
            $branch = $this->branchInterfaceFactory->create();
            $this->resource->load($branch, $idCi, BranchInterface::CODE);
            if (!$branch->getId()) {
                throw new NoSuchEntityException(__('Requested Branch doesn\'t exist'));
            }
            $this->instances['ext' . $idCi] = $branch;
        }
        return $this->instances['ext' . $idCi];
    }
    /**
     * Retrieve Branches matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Gento\Oca\Api\Data\BranchSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Gento\Oca\Api\Data\BranchSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Gento\Oca\Model\ResourceModel\Branch\Collection $collection */
        $collection = $this->branchCollectionFactory->create();

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
            $collection->addOrder('main_table.' . BranchInterface::BRANCH_ID, SortOrder::SORT_ASC);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var BranchInterface[] $branches */
        $branches = [];
        /** @var \Gento\Oca\Model\Branch $branch */
        foreach ($collection as $branch) {
            /** @var BranchInterface $branchDataObject */
            $branchDataObject = $this->branchInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $branchDataObject,
                $branch->getData(),
                BranchInterface::class
            );
            $branches[] = $branchDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($branches);
    }

    /**
     * Delete Branch
     *
     * @param BranchInterface $branch
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BranchInterface $branch)
    {
        /** @var BranchInterface|\Magento\Framework\Model\AbstractModel $branch */
        $id = $branch->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($branch);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to removeBranch %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Branch by ID.
     *
     * @param int $branchId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($branchId)
    {
        $branch = $this->get($branchId);
        return $this->delete($branch);
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
