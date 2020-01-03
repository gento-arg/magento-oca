<?php
namespace Gento\Oca\Controller\Adminhtml\Branch;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Api\Data\BranchInterfaceFactory;
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
     * Branch factory
     * @var BranchInterfaceFactory
     */
    protected $branchFactory;
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
     * Branch repository
     * @var BranchRepositoryInterface
     */
    protected $branchRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param BranchInterfaceFactory $branchFactory
     * @param BranchRepositoryInterface $branchRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        BranchInterfaceFactory $branchFactory,
        BranchRepositoryInterface $branchRepository,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        Registry $registry
    ) {
        $this->branchFactory = $branchFactory;
        $this->branchRepository = $branchRepository;
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
        /** @var BranchInterface $branch */
        $branch = null;
        $postData = $this->getRequest()->getPostValue();
        $data = $postData;
        $id = !empty($data['branch_id']) ? $data['branch_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $branch = $this->branchRepository->get((int)$id);
            } else {
                unset($data['branch_id']);
                $branch = $this->branchFactory->create();
            }
            $this->dataObjectHelper->populateWithArray($branch, $data, BranchInterface::class);
            $this->branchRepository->save($branch);
            $this->messageManager->addSuccessMessage(__('You saved the Branch'));
            $this->dataPersistor->clear('gento_oca_branch');
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('*/*/edit', ['branch_id' => $branch->getId()]);
            } else {
                $resultRedirect->setPath('*/*');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('gento_oca_branch', $postData);
            $resultRedirect->setPath('*/*/edit', ['branch_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the Branch'));
            $this->dataPersistor->set('gento\oca_branch', $postData);
            $resultRedirect->setPath('*/*/edit', ['branch_id' => $id]);
        }
        return $resultRedirect;
    }
}
