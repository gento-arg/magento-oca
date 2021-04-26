define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../model/shipping-rates-validator',
    '../model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    shippingRatesValidator,
    shippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('gento_oca', shippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('gento_oca', shippingRatesValidationRules);

    return Component;
});
