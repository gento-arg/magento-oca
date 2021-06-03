<?php
namespace Gento\Oca\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface OperatorySearchResultInterface
{
    /**
     * get items
     *
     * @return \Gento\Oca\Api\Data\OperatoryInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Gento\Oca\Api\Data\OperatoryInterface[] $items
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
