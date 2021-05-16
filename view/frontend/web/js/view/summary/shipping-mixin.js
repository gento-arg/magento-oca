define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, registry, quote) {
    'use strict';

    return function (target) {
        return target.extend({
            getShippingMethodTitle: function () {
                var shippingMethodTitle = this._super(),
                    shippingMethod = quote.shippingMethod();

                if (shippingMethod && shippingMethod['carrier_code'] === 'gento_oca' &&
                    quote.shippingAddress()['extension_attributes'] &&
                    quote.shippingAddress()['extension_attributes']['gento_oca_branch_description']) {
                    shippingMethodTitle = shippingMethodTitle + ': ' + quote.shippingAddress()['extension_attributes']['gento_oca_branch_description'];
                }

                return shippingMethodTitle;
            }
        });
    };
});
