/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/full-screen-loader'
],
    function ($, authenticationPopup, customerData, urlBuilder, storage, fullScreenLoader) {
        'use strict';

        return function (config, element) {
            var serviceUrl = urlBuilder.createUrl('/affirm/checkout/payment', {});
            $(element).click(function () {
                fullScreenLoader.startLoader();
                storage.post(
                        serviceUrl
                    ).done(
                    function (response) {
                        if (response) {
                        $.mage.redirect(config.checkoutUrl);
                        }
                    }
                ).fail(
                    function (response) {
                        fullScreenLoader.stopLoader();
                    }
                );
            });
        };
    }
);
