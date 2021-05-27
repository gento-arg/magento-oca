<?php

namespace Gento\Oca\Model\Config\Source;

use Gento\Oca\Api\HistoryRepositoryInterface;

class HistoryStatus extends AbstractSource
{
    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * HistoryService constructor.
     * @param HistoryRepositoryInterface $historyRepository
     */
    public function __construct(
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->historyRepository = $historyRepository;
    }

    /**
     * @inheridoc
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->historyRepository->getStatusList() as $item) {
            $items[$item] = $item;
        }
        return $items;
    }
}
