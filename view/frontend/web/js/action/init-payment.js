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
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messages'
],
    function ($, authenticationPopup, customerData, urlBuilder, storage, errorProcessor, Messages) {
        'use strict';

        return function (config, element) {
            var serviceUrl = urlBuilder.createUrl('/affirm/checkout/payment', {});
            $(element).click(function (event) {
                var _self = this;
                event.preventDefault();
                storage.post(
                        serviceUrl
                    ).done(
                    function (response) {
                        if (response) {
                            location.href = config.checkoutUrl;
                        }
                    }
                ).fail(
                    function (response) {
                        _self.messageContainer = Messages();
                        errorProcessor.process(response, _self.messageContainer);
                    }
                );
            });
        };
    }
);
