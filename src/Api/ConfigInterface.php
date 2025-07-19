<?php
declare(strict_types = 1);

namespace Gento\Oca\Api;

interface ConfigInterface
{
    public const PREFIX = 'carriers/gento_oca';
    public const XPATH_DISABLED_CP = 'disabled_cp';
    public const XPATH_SHOW_DAYS = 'show_days';
    public const XPATH_CODE = 'code';
    public const XPATH_TRACKING_URL = 'tracking_url';
    public const XPATH_VOLUME_MIN = 'volume/min';
    public const XPATH_SHOWMETHOD = 'showmethod';
    public const XPATH_TITLE = 'title';
    public const XPATH_SPECIFIC_ERROR_MESSAGE = 'specificerrmsg';
    public const XPATH_ACTIVE = 'active';
    public const XPATH_HISTORY_LIMIT = 'history_limit';
    public const XPATH_BRANCH_DESCRIPTION_TEMPLATE = 'branch_description';
    public const XPATH_BRANCH_AUTOPOPULATE = 'branch_autopopulate';
    public const XPATH_RECEPTION_TIME = 'reception_time';
    public const XPATH_DAYS_EXTRA = 'days_extra';

    /**
     * Return a list of branches with description
     *
     * @param array $branches
     *
     * @return array[]
     */
    public function addDescriptionToBranches(array $branches): array;

    /**
     * Return if the branches must be autopopulate
     *
     * @return bool
     */
    public function getBranchAutoPopulate(): bool;

    /**
     * Return days to add on the estimated arrival
     *
     * @return int
     */
    public function getDaysExtra(): int;

    /**
     * Return the days limit to keep history
     *
     * @return int
     */
    public function getHistoryLimit(): int;

    /**
     * Return an array with the product size
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int[]
     */
    public function getProductSize(\Magento\Catalog\Model\Product $product): array;

    /**
     * Return the reception time
     *
     * @return string
     */
    public function getReceptionTime(): string;
}
