<?php
namespace Gento\Oca\Model\Branch;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Gento\Oca\Model\ResourceModel\Branch\CollectionFactory as BranchCollectionFactory;

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
     * @param BranchCollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        BranchCollectionFactory $collectionFactory,
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
        /** @var \Gento\Oca\Model\Branch $branch */
        foreach ($items as $branch) {
            $this->loadedData[$branch->getId()] = $branch->getData();
        }
        $data = $this->dataPersistor->get('gento_oca_branch');
        if (!empty($data)) {
            $branch = $this->collection->getNewEmptyItem();
            $branch->setData($data);
            $this->loadedData[$branch->getId()] = $branch->getData();
            $this->dataPersistor->clear('gento_oca_branch');
        }
        return $this->loadedData;
    }
}
