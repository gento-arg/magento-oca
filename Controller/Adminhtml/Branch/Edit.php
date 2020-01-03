<?php
namespace Gento\Oca\Controller\Adminhtml\Branch;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Gento\Oca\Api\BranchRepositoryInterface;

class Edit extends Action
{
    /**
     * @var BranchRepositoryInterface
     */
    private $branchRepository;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param BranchRepositoryInterface $branchRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        BranchRepositoryInterface $branchRepository,
        Registry $registry
    ) {
        $this->branchRepository = $branchRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * get current Branch
     *
     * @return null|\Gento\Oca\Api\Data\BranchInterface
     */
    private function initBranch()
    {
        $branchId = $this->getRequest()->getParam('branch_id');
        try {
            $branch = $this->branchRepository->get($branchId);
        } catch (NoSuchEntityException $e) {
            $branch = null;
        }
        $this->registry->register('current_branch', $branch);
        return $branch;
    }

    /**
     * Edit or create Branch
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $branch = $this->initBranch();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Gento_Oca::branches');
        $resultPage->getConfig()->getTitle()->prepend(__('Branches'));

        if ($branch === null) {
            $resultPage->getConfig()->getTitle()->prepend(__('New Branch'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend($branch->getName());
        }
        return $resultPage;
    }
}
