<?php

namespace Gento\Oca\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface HistorySearchResultInterface
{
    /**
     * get items
     *
     * @return HistoryInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param HistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);
}
