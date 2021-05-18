<?php

namespace Gento\Oca\Controller\Ajax;

use Gento\Oca\Helper\Data;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Catalog index page controller.
 */
class Branches extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var OcaApi
     */
    protected $ocaApi;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Branches constructor.
     * @param JsonFactory $resultJsonFactory
     * @param OcaApi $ocaApi
     * @param Context $context
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        OcaApi $ocaApi,
        Context $context,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ocaApi = $ocaApi;
        $this->helper = $helper;
        parent::__construct($context);
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $zipcode = $this->getRequest()->getParam('zipcode');
        $branches = $this->ocaApi->getBranchesZipCode($zipcode);
        $branches = $this->helper->addDescriptionToBranches($branches);

        if ($this->getBranchAutoPopulate()) {
            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $branches
            ]);
        }

        return $result->setData($branches);
    }

    /**
     * @return mixed
     */
    protected function getBranchAutoPopulate()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/branch_autopopulate');
    }
}
