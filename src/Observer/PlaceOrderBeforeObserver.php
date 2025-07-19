<?php

declare(strict_types = 1);

namespace Gento\Oca\Observer;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Throwable;

class PlaceOrderBeforeObserver implements ObserverInterface
{
    /**
     * @param BranchRepositoryInterface $branchRepository
     * @param QuoteRepository $quoteRepository
     * @param OcaApi $ocaApi
     * @param ConfigInterface $config
     */
    public function __construct(
        readonly private BranchRepositoryInterface $branchRepository,
        readonly private QuoteRepository $quoteRepository,
        readonly private OcaApi $ocaApi,
        readonly private ConfigInterface $config,
    ) {
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws Throwable
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        $shippingMethod = $order->getShippingMethod();
        if (substr($shippingMethod ?? '', 0, 9) !== 'gento_oca') {
            return $this;
        }

        /* @var Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        $originBranchCode = $quote->getData('shipping_origin_branch');
        $order->setData('shipping_origin_branch', $originBranchCode);

        $branchData = null;
        try {
            $shippingBranch = $quote->getData('shipping_branch');

            $order->setData('shipping_branch', $shippingBranch);

            $branch = $this->branchRepository->getByCode($shippingBranch);
            $branchData = $branch->getData();
        } catch (NoSuchEntityException $e) {
        }

        if ($branchData === null) {
            $postcode = $quote->getShippingAddress()->getPostcode();
            $branches = $this->ocaApi->getBranchesWithServiceZipCode($postcode);
            foreach ($branches as $branch) {
                if ($branch['code'] == $shippingBranch) {
                    $branchData = $branch;
                    break;
                }
            }
        }

        if ($branchData !== null) {
            $branchData = $this->config->addDescriptionToBranch($branchData);
            $branchDescription = trim($branchData['branch_description']);
            if (!empty($branchDescription)) {
                $shippingDescription = $order->getShippingDescription() . PHP_EOL . $branchData['branch_description'];
                $order->setShippingDescription($shippingDescription);
            }
        }

        return $this;
    }
}
