<?php

namespace Gento\Oca\Controller\Ajax;

use Gento\Oca\Service\Branch;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Throwable;

class Branches implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Branch
     */
    private $branchService;

    /**
     * Branches constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param Context     $context
     * @param Branch      $branchService
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        Branch $branchService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $context->getRequest();
        $this->branchService = $branchService;
    }

    /**
     * @throws Throwable
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $zipcode = $this->request->getParam('zipcode');
        $branches = $this->branchService->getBranches($zipcode);

        return $result->setData($branches);
    }

}
