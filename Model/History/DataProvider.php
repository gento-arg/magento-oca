<?php

namespace Gento\Oca\Model\History;

use Gento\Oca\Model\History;
use Gento\Oca\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * Loaded data cache
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Data persistor
     *
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param HistoryCollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        HistoryCollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var History $history */
        foreach ($items as $history) {
            $this->loadedData[$history->getId()] = $history->getData();
        }
        $data = $this->dataPersistor->get('gento_oca_history');
        if (!empty($data)) {
            $history = $this->collection->getNewEmptyItem();
            $history->setData($data);
            $this->loadedData[$history->getId()] = $history->getData();
            $this->dataPersistor->clear('gento_oca_history');
        }
        return $this->loadedData;
    }
}
