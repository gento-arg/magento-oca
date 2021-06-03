<?php
namespace Gento\Oca\Controller\Adminhtml\Operatory;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Gento\Oca\Api\OperatoryRepositoryInterface;

class Edit extends Action
{
    /**
     * @var OperatoryRepositoryInterface
     */
    private $operatoryRepository;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param OperatoryRepositoryInterface $operatoryRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        OperatoryRepositoryInterface $operatoryRepository,
        Registry $registry
    ) {
        $this->operatoryRepository = $operatoryRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * get current Operatory
     *
     * @return null|\Gento\Oca\Api\Data\OperatoryInterface
     */
    private function initOperatory()
    {
        $operatoryId = $this->getRequest()->getParam('operatory_id');
        try {
            $operatory = $this->operatoryRepository->get($operatoryId);
        } catch (NoSuchEntityException $e) {
            $operatory = null;
        }
        $this->registry->register('current_operatory', $operatory);
        return $operatory;
    }

    /**
     * Edit or create Operatory
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $operatory = $this->initOperatory();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Gento_Oca::operatories');
        $resultPage->getConfig()->getTitle()->prepend(__('Operatories'));

        if ($operatory === null) {
            $resultPage->getConfig()->getTitle()->prepend(__('New Operatory'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend($operatory->getName());
        }
        return $resultPage;
    }
}
