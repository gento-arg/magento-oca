<?php

namespace Gento\Oca\Controller\Adminhtml\History;

use Gento\Oca\Api\Data\HistoryInterface;
use Gento\Oca\Api\HistoryRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class View extends Action
{
    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param HistoryRepositoryInterface $historyRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        HistoryRepositoryInterface $historyRepository,
        Registry $registry
    ) {
        $this->historyRepository = $historyRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * View History
     *
     * @return Page
     */
    public function execute()
    {
        $this->initHistory();
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Gento_Oca::history');
        $resultPage->getConfig()->getTitle()->prepend(__('History'));
        $resultPage->getConfig()->getTitle()->prepend(__('View Details'));
        return $resultPage;
    }

    /**
     * get current History
     *
     * @return null|HistoryInterface
     */
    private function initHistory()
    {
        $historyId = $this->getRequest()->getParam('id');
        try {
            $history = $this->historyRepository->get($historyId);
        } catch (NoSuchEntityException $e) {
            $history = null;
        }
        $this->registry->register('current_history', $history);
        return $history;
    }
}
