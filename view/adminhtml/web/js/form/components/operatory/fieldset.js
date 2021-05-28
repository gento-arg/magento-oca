define([
    'underscore',
    'Magento_Ui/js/form/components/fieldset'
], function (
    _,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            hasOriginBranch: false,
            operatoryType: '',
        },
        initObservable: function () {
            this._super()
            this.observe('hasOriginBranch operatoryType');

            this.operatoryType.subscribe((v) => {
                this.hasOriginBranch(v == 'branch2door' || v == 'branch2branch');
            });

            return this;
        }
    });
});
