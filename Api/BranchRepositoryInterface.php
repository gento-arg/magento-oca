<?php
namespace Gento\Oca\Api;

use Gento\Oca\Api\Data\BranchInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface BranchRepositoryInterface
{
    /**
     * @param BranchInterface $Branch
     * @return BranchInterface
     */
    public function save(BranchInterface $Branch);

    /**
     * @param $id
     * @return BranchInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gento\Oca\Api\Data\BranchSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param BranchInterface $Branch
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BranchInterface $Branch);

    /**
     * @param int $BranchId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($BranchId);

    /**
     * clear caches instances
     * @return void
     */
    public function clear();
}
