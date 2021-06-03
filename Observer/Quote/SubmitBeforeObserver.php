<?php

namespace Gento\Oca\Observer\Quote;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Helper\Data;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class SubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var BranchRepositoryInterface
     */
    private $branchRepository;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var OcaApi
     */
    private $ocaApi;

    /**
     * SubmitBeforeObserver constructor.
     * @param BranchRepositoryInterface $branchRepository
     * @param Data $helper
     * @param OcaApi $ocaApi
     */
    public function __construct(
        BranchRepositoryInterface $branchRepository,
        Data $helper,
        OcaApi $ocaApi
    ) {
        $this->branchRepository = $branchRepository;
        $this->helper = $helper;
        $this->ocaApi = $ocaApi;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $branchCode = $quote->getData('shipping_branch');
        $order->setData('shipping_branch', $branchCode);
        $originBranchCode = $quote->getData('shipping_origin_branch');
        $order->setData('shipping_origin_branch', $originBranchCode);

        $branchData = null;
        try {
            $branch = $this->branchRepository
                ->getByCode($branchCode);
            $branchData = $branch->getData();
        } catch (NoSuchEntityException $e) {
        }

        if ($branchData === null) {
            $postcode = $quote->getShippingAddress()->getPostcode();
            $branches = $this->ocaApi->getBranchesWithServiceZipCode($postcode);
            foreach ($branches as $branch) {
                if ($branch['code'] == $branchCode) {
                    $branchData = $branch;
                    break;
                }
            }
        }

        if ($branchData !== null) {
            $branchData = $this->helper->addDescriptionToBranch($branchData);
            $branchDescription = trim($branchData['branch_description']);

            $shippingDescription = $order->getShippingDescription() . PHP_EOL . $branchDescription;
            $order->setShippingDescription($shippingDescription);
        }

        return $this;
    }

}
