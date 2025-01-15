<?php

namespace Gento\Oca\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const XPATH_HISTORY_LIMIT = 'carriers/gento_oca/history_limit';
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return the days to keep on request history
     *
     * @return mixed
     */
    public function getHistoryLimit()
    {
        return $this->scopeConfig->getValue(self::XPATH_HISTORY_LIMIT);
    }
}
