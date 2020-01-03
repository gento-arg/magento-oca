<?php
namespace Gento\Oca\Api;

use Gento\Oca\Api\Data\OperatoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface OperatoryRepositoryInterface
{
    /**
     * @param OperatoryInterface $Operatory
     * @return OperatoryInterface
     */
    public function save(OperatoryInterface $Operatory);

    /**
     * @param $id
     * @return OperatoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gento\Oca\Api\Data\OperatorySearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param OperatoryInterface $Operatory
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(OperatoryInterface $Operatory);

    /**
     * @param int $OperatoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($OperatoryId);

    /**
     * clear caches instances
     * @return void
     */
    public function clear();
}
