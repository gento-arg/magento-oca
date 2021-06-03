define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function (
    $,
    storage
) {
    'use strict';

    var cacheKey = 'oca-data',

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        initData = function () {
            return {
                'selectedOcaBranch': null
            };
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = $.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey);

                if ($.isEmptyObject(data)) {
                    data = initData();
                    saveData(data);
                }
            }

            return data;
        };

    return {
        setSelectedOcaBranch: function (data) {
            var obj = getData();

            obj.selectedOcaBranch = data;
            saveData(obj);
        },

        getSelectedOcaBranch: function () {
            return getData().selectedOcaBranch;
        },
    }
})
