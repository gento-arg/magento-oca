define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function (
    wrapper,
    quote
) {
    'use strict';

    return function (overriddenFunction) {
        return wrapper.wrap(overriddenFunction, function (originalAction) {
            let payload = originalAction();

            let shippingMethod = quote.shippingMethod();

            if (shippingMethod['extension_attributes'] &&
                shippingMethod['extension_attributes']['gento_oca_branch']) {
                payload.addressInformation['extension_attributes']['gento_oca_branch'] =
                    shippingMethod['extension_attributes']['gento_oca_branch'];
            }

            return payload;
        });
    };
});
