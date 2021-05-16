<?php

namespace Gento\Oca\Controller\Ajax;

use Gento\Oca\Helper\Data;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

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
     * Branches constructor.
     * @param JsonFactory $resultJsonFactory
     * @param OcaApi $ocaApi
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        OcaApi $ocaApi,
        Context $context,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ocaApi = $ocaApi;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $zipcode = $this->getRequest()->getParam('zipcode');
        $branches = $this->ocaApi->getBranchesZipCode($zipcode);
        $branches = $this->helper->addDescriptionToBranches($branches);

        return $result->setData($branches);
    }
}
