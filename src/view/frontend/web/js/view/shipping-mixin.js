define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    _,
    quote,
    $t
) {
    'use strict';

    return function (target) {
        return target.extend({
            /** @inheritdoc */
            validateShippingInformation: function () {
                if (this._super() === false) {
                    return false;
                }

                let useIdci = window.checkoutConfig.oca.useBranches;
                let shippingAddress = quote.shippingAddress();
                let shippingMethod = quote.shippingMethod();
                // "shippingAddress[*] ?? " Magento <2.4 compat
                let carrier = shippingAddress['carrier_code'] ?? shippingMethod['carrier_code'];
                let method = shippingAddress['method_code'] ?? shippingMethod['method_code'];

                if (carrier !== 'gento_oca' || !useIdci.includes(method)) {
                    return true;
                }

                if (!shippingAddress['extension_attributes'] ||
                    !shippingAddress['extension_attributes']['gento_oca_branch']) {
                    this.errorValidationMessage($t('Please select the required branch.'));
                    return false;
                }

                return true;
            }
        });
    };
});
