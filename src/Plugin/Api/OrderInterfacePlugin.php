<?php

namespace Gento\Oca\Plugin\Api;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;

class OrderInterfacePlugin
{
    public function aroundGetShippingMethod(
        OrderInterface $order,
        \Closure $proceed,
        $asObject = false
    ) {
        $shippingMethod = $order->getData('shipping_method');
        if (!preg_match('/^gento_oca/', $shippingMethod)) {
            return $proceed($asObject);
        }

        if (!$asObject || !$shippingMethod) {
            return $shippingMethod;
        } else {
            $carrierCode = 'gento_oca';
            $method = preg_replace('/^gento_oca_(.*)$/', '$1', $shippingMethod);
            return new DataObject(['carrier_code' => $carrierCode, 'method' => $method]);
        }
    }
}
