<?php

namespace Gento\Oca\Controller\Ajax;

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

    public function __construct(
        JsonFactory $resultJsonFactory,
        OcaApi $ocaApi,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ocaApi = $ocaApi;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $zipcode = $this->getRequest()->getParam('zipcode');
        $branches = $this->ocaApi->getBranchesZipCode($zipcode);

        return $result->setData($branches);
    }
}
