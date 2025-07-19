<?php

declare(strict_types = 1);

namespace Gento\Oca\Model\Cron\History;

use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Api\HistoryRepositoryInterface;
use Gento\Oca\Model\Config;

class Clean
{
    /**
     * @param Config $config
     * @param HistoryRepositoryInterface $historyRepository
     */
    public function __construct(
        private ConfigInterface $config,
        private HistoryRepositoryInterface $historyRepository
    ) {
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->historyRepository->deleteHistories($this->config->getHistoryLimit());
    }
}
