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

        return function () {
            var  serviceUrl = urlBuilder.createUrl('/affirm/checkout/verify', {}), result;
            fullScreenLoader.startLoader();
            result = storage.post(serviceUrl).done(
                function(response) {
                    return response;
                }).fail(
                    function () {
                });
            fullScreenLoader.stopLoader();
            return result;
        };
    }
);
