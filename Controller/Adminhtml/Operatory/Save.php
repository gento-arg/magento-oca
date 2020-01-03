<?php
namespace Gento\Oca\Controller\Adminhtml\Operatory;

use Gento\Oca\Api\OperatoryRepositoryInterface;
use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Api\Data\OperatoryInterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * Operatory factory
     * @var OperatoryInterfaceFactory
     */
    protected $operatoryFactory;
    /**
     * Data Object Processor
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    /**
     * Data Object Helper
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * Data Persistor
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * Core registry
     * @var Registry
     */
    protected $registry;
    /**
     * Operatory repository
     * @var OperatoryRepositoryInterface
     */
    protected $operatoryRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param OperatoryInterfaceFactory $operatoryFactory
     * @param OperatoryRepositoryInterface $operatoryRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        OperatoryInterfaceFactory $operatoryFactory,
        OperatoryRepositoryInterface $operatoryRepository,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        Registry $registry
    ) {
        $this->operatoryFactory = $operatoryFactory;
        $this->operatoryRepository = $operatoryRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * run the action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var OperatoryInterface $operatory */
        $operatory = null;
        $postData = $this->getRequest()->getPostValue();
        $data = $postData;
        $id = !empty($data['operatory_id']) ? $data['operatory_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $operatory = $this->operatoryRepository->get((int)$id);
            } else {
                unset($data['operatory_id']);
                $operatory = $this->operatoryFactory->create();
            }
            $this->dataObjectHelper->populateWithArray($operatory, $data, OperatoryInterface::class);
            $this->operatoryRepository->save($operatory);
            $this->messageManager->addSuccessMessage(__('You saved the Operatory'));
            $this->dataPersistor->clear('gento_oca_operatory');
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('*/*/edit', ['operatory_id' => $operatory->getId()]);
            } else {
                $resultRedirect->setPath('*/*');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('gento_oca_operatory', $postData);
            $resultRedirect->setPath('*/*/edit', ['operatory_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the Operatory'));
            $this->dataPersistor->set('gento\oca_operatory', $postData);
            $resultRedirect->setPath('*/*/edit', ['operatory_id' => $id]);
        }
        return $resultRedirect;
    }
}
