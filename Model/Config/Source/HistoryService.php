<?php

namespace Gento\Oca\Model\Config\Source;

use Gento\Oca\Api\HistoryRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class HistoryService implements OptionSourceInterface
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
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return array_map(function ($key, $label) {
            return ['value' => $key, 'label' => $label];
        }, array_keys($this->toArray()), $this->toArray());
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->historyRepository->getServicesList() as $item) {
            $items[$item] = $item;
        }
        return $items;
    }
}
