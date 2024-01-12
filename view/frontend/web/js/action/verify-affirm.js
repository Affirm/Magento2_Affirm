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
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor'
],
    function ($, authenticationPopup, customerData, urlBuilder, storage, fullScreenLoader, errorProcessor) {
        'use strict';

        return function (messageContainer) {
            var  serviceUrl = urlBuilder.createUrl('/affirm/checkout/verify', {}), result;
            fullScreenLoader.startLoader();
            result = storage.post(serviceUrl).done(
                function(response) {
                    return response;
                }).fail(
                    function (response) {
                        errorProcessor.process(response, messageContainer);
                });
            fullScreenLoader.stopLoader();
            return result;
        };
    }
);
