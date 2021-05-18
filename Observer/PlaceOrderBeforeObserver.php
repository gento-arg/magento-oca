<?php

namespace Gento\Oca\Observer;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PlaceOrderBeforeObserver implements ObserverInterface
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
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * SubmitBeforeObserver constructor.
     * @param BranchRepositoryInterface $branchRepository
     * @param Data $helper
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        BranchRepositoryInterface $branchRepository,
        Data $helper,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->branchRepository = $branchRepository;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        $shippingMethod = $order->getShippingMethod();
        if (substr($shippingMethod, 0, 9) !== 'gento_oca') {
            return $this;
        }

        /* @var Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        try {
            $shippingBranch = $quote->getData('shipping_branch');

            $order->setData('shipping_branch', $shippingBranch);

            $branch = $this->branchRepository->getByCode($shippingBranch);
            $branchData = $branch->getData();
            $branchData = $this->helper->addDescriptionToBranch($branchData);
            if (!empty($branchData['branch_description'])) {
                $shippingDescription = $order->getShippingDescription() . PHP_EOL . $branchData['branch_description'];
                $order->setShippingDescription($shippingDescription);
            }
        } catch (NoSuchEntityException $e) {
        }

        return $this;
    }

}
