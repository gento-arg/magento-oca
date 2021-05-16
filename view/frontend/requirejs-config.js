var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Gento_Oca/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Gento_Oca/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Gento_Oca/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/summary/shipping': {
                'Gento_Oca/js/view/summary/shipping-mixin': true
            }
        }
    }
};
