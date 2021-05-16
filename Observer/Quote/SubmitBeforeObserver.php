<?php

namespace Gento\Oca\Observer\Quote;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Helper\Data;
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

    public function __construct(
        BranchRepositoryInterface $branchRepository,
        Data $helper
    ) {
        $this->branchRepository = $branchRepository;
        $this->helper = $helper;
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

        $order->setData('shipping_branch', $quote->getData('shipping_branch'));
        try {
            $branch = $this->branchRepository
                ->getByCode($quote->getData('shipping_branch'));
            $branchData = $branch->getData();
            $branchData = $this->helper->addDescriptionToBranch($branchData);
            $shippingDescription = $order->getShippingDescription() . PHP_EOL . $branchData['branch_description'];
            $order->setShippingDescription($shippingDescription);
        } catch (NoSuchEntityException $e) {
        }

        return $this;
    }

}