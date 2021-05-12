define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Gento_Oca/js/service/branch',
    'mage/translate'
], function ($,
             Component,
             ko,
             quote,
             serviceBranch) {
    'use strict';

    var useIdci = window.checkoutConfig.oca.useBranches;

    return Component.extend({
        defaults: {
            template: 'Gento_Oca/branch-selector'
        },
        visible: ko.observable(false),
        selectedBranch: ko.observable(),
        useIdci: ko.observable(),
        branchList: ko.observableArray([]),
        branchesCache: ko.observable({}),
        hasWarning: ko.observable(true),
        initObservable: function () {
            this._super();
            quote.shippingAddress.subscribe(() => {
                this.loadBranches(quote.shippingAddress().postcode)
            });
            quote.shippingMethod.subscribe(() => {
                let carrierMethod = this.getCarrierAndMethod();
                if (carrierMethod.length == 0) {
                    return;
                }
                let carrier = carrierMethod[0],
                    method = carrierMethod[1];

                this.visible(!quote.isVirtual() && carrier == 'gento_oca' && useIdci.includes(method))
                this.loadBranches(quote.shippingAddress().postcode)
            });
            this.selectedBranch.subscribe(() => {
                this.hasWarning(!this.selectedBranch())
                let ocaBranch = quote.shippingAddress().customAttributes
                    .find(e => e.attribute_code == 'gento_oca_banch');
                if (ocaBranch == undefined) {
                    ocaBranch = {
                        attribute_code: 'gento_oca_banch',
                    };
                    quote.shippingAddress().customAttributes.push(ocaBranch)
                }
                ocaBranch.value = null;
                if (this.selectedBranch()) {
                    ocaBranch.value = this.selectedBranch().code;
                }
            })
            return this;
        },

        getCarrierAndMethod: function () {
            let shippingMethod = quote.shippingMethod();
            if (shippingMethod == null) {
                return [];
            }
            let carrier = shippingMethod['carrier_code'];
            let method = shippingMethod['method_code'];
            return [carrier, method];
        },

        loadBranches: function (postcode) {
            let carrierMethod = this.getCarrierAndMethod();
            if (carrierMethod.length == 0) {
                return;
            }
            let method = carrierMethod[1];
            if (!useIdci.includes(method)) {
                return;
            }

            if (this.branchesCache[postcode]) {
                this.branchList(this.branchesCache[postcode]);
                return;
            }
            this.ajaxBranches(postcode)
                .then((data) => {
                    this.branchesCache[postcode] = data.sort((a, b) => {
                        let key1 = a.zipcode + '-' + a.name;
                        let key2 = b.zipcode + '-' + b.name;

                        if (key1 < key2)
                            return -1;
                        if (key1 > key2)
                            return 1;

                        return 0;
                    });
                    this.branchList(this.branchesCache[postcode]);
                })
        },

        ajaxBranches: function (postcode) {
            return serviceBranch.getRates(postcode);
        },

        itemLabel: function (item) {
            return `${item.zipcode} - ${item.name}`;
        },

        selectedBranchAddress: function () {
            let branch = this.selectedBranch();

            return `${branch.address_street} ${branch.address_number}`;
        }
    });
});