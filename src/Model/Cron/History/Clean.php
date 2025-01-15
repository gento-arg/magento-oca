<?php

namespace Gento\Oca\Model\Cron\History;

use Gento\Oca\Api\HistoryRepositoryInterface;
use Gento\Oca\Model\Config;

class Clean
{
    /**
     * @var Config
     */
    private Config $config;
    private HistoryRepositoryInterface $historyRepository;

    /**
     * @param Config $config
     * @param HistoryRepositoryInterface $historyRepository
     */
    public function __construct(
        Config $config,
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->config = $config;
        $this->historyRepository = $historyRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->historyRepository->deleteHistories($this->config->getHistoryLimit());
    }
}
