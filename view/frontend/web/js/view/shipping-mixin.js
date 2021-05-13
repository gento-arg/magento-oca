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
                let shippingMethod = quote.shippingMethod();
                let carrier = shippingMethod['carrier_code'];
                let method = shippingMethod['method_code'];

                if (carrier != 'gento_oca' || !useIdci.includes(method)) {
                    return true;
                }

                if (!shippingMethod['extension_attributes'] ||
                    !shippingMethod['extension_attributes']['gento_oca_branch']) {
                    this.errorValidationMessage($t('Please select the required branch.'));
                    return false;
                }

                return true;
            }
        });
    };
});
