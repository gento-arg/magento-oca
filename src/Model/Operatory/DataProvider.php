<?php
namespace Gento\Oca\Model\Operatory;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory as OperatoryCollectionFactory;

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
     * @param OperatoryCollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        OperatoryCollectionFactory $collectionFactory,
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
        /** @var \Gento\Oca\Model\Operatory $operatory */
        foreach ($items as $operatory) {
            $this->loadedData[$operatory->getId()] = $operatory->getData();
        }
        $data = $this->dataPersistor->get('gento_oca_operatory');
        if (!empty($data)) {
            $operatory = $this->collection->getNewEmptyItem();
            $operatory->setData($data);
            $this->loadedData[$operatory->getId()] = $operatory->getData();
            $this->dataPersistor->clear('gento_oca_operatory');
        }
        return $this->loadedData;
    }
}
