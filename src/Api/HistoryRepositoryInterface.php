<?php

namespace Gento\Oca\Api;

use Gento\Oca\Api\Data\HistoryInterface;
use Gento\Oca\Api\Data\HistorySearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface HistoryRepositoryInterface
{
    /**
     * @param HistoryInterface $item
     * @return HistoryInterface
     */
    public function save(HistoryInterface $item);

    /**
     * @param $id
     * @return HistoryInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return HistorySearchResultInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return string[]
     */
    public function getServicesList(): array;

    /**
     * @return string[]
     */
    public function getStatusList(): array;

    /**
     * clear caches instances
     * @return void
     */
    public function clear();

    /**
     * Delete olds history requests
     *
     * @param $dayLimit
     * @return mixed
     */
    public function deleteHistories($dayLimit);
}
