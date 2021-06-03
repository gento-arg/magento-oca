define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';

    var branchesUrl = window.checkoutConfig.oca.branchesUrl;

    return {
        getRates: function (postcode) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    showLoader: true,
                    url: branchesUrl,
                    data: {'zipcode': postcode},
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        resolve(data)
                    },
                    error: function (data) {
                        reject(data)
                    }
                });
            });
        }
    };
})
