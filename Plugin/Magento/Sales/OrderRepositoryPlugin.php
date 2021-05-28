<?php

namespace Gento\Oca\Plugin\Magento\Sales;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        return $this->addExtensionAttributes($entity);
    }

    /**
     * @param $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     */
    public function afterGetList($subject, $result)
    {
        foreach ($result->getItems() as $order) {
            $this->addExtensionAttributes($order);
        }
        return $result;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addExtensionAttributes(OrderInterface $order)
    {
        /** @var $extensionAttributes OrderExtensionInterface */
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();

        $extensionAttributes->setShippingBranch($order->getData('shipping_branch'));
        $extensionAttributes->setShippingOriginBranch($order->getData('shipping_origin_branch'));
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

}
