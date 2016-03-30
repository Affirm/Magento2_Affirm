/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'affirmCheckout',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'synchPost',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messages'
    ],
    function (Component, AffirmCheckout, quote, additionalValidator, url,
              storage, urlBuilder, customer, errorProcessor, Messages) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnePica_Affirm/payment/form',
                transactionResult: ''
            },

            /**
             * Init Affirm specify message controller
             */
            initAffirm: function() {
                this.messageContainer = new Messages();
            },

            /**
             * Payment code
             *
             * @returns {string}
             */
            getCode: function () {
                return 'affirm_gateway';
            },

            /**
             * Add data about order id
             * Send request with quote data to REST API for get actual information
             */
            addIncrementId: function(checkoutData) {
                var serviceUrl, messageContainer = this.messageContainer;
                if (!customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/affirm/order/guest/load/:quoteId', {quoteId: quote.getQuoteId()});
                } else {
                    //Send request if the customer is Logged In
                    serviceUrl = urlBuilder.createUrl('/affirm/order/load/:quoteId', {quoteId: quote.getQuoteId()});
                }

                // Send post request for getting data from order
                storage.post(serviceUrl).done(function(response) {
                    checkoutData.order_id = response;
                }).fail(function() {
                    errorProcessor.process(response, messageContainer);
                });
            },

            /**
             * After place order handler
             */
            afterPlaceOrder: function () {
                var checkoutData = {};
                // Init all data for the sending request
                this.initAffirm();
                this.initBillingData(checkoutData);
                this.initShippingData(checkoutData);
                this.initMerchantData(checkoutData);
                this.initConfigData(checkoutData);
                this.initAdditionalData(checkoutData);
                this.initItems(checkoutData);
                this.initCheckoutTotals(checkoutData);
                this.addIncrementId(checkoutData);

                if (additionalValidator.validate()) {
                    // setup and configure checkout
                    affirm.checkout(checkoutData);
                    affirm.checkout.post();
                    affirm.ui.error.on("close", function() {
                        window.location= window.checkoutConfig.payment['affirm_gateway'].merchant.cancelUrl;
                    });
                }
            },

            /**
             * Init merchant Data
             *
             * @param checkoutDataObject
             */
            initMerchantData: function(checkoutDataObject) {
                var confUrl = window.checkoutConfig.payment['affirm_gateway'].merchant.confirmationUrl,
                    cancelUrl = window.checkoutConfig.payment['affirm_gateway'].merchant.cancelUrl;
                // Specify merchant data
                checkoutDataObject.merchant = checkoutDataObject.merchant || {};
                checkoutDataObject.merchant.user_confirmation_url = confUrl;
                checkoutDataObject.merchant.user_cancel_url = cancelUrl;
            },

            /**
             * Init config data
             * @param checkoutDataObject
             */
            initConfigData: function(checkoutDataObject) {
                var financialKey = window.checkoutConfig.payment['affirm_gateway'].config.financialKey;
                checkoutDataObject.config = checkoutDataObject.config || {};
                checkoutDataObject.config.financial_product_key = financialKey;
            },

            /**
             * Init quote shipping data
             *
             * @param checkoutDataObject
             */
            initShippingData: function(checkoutDataObject) {
                var shippingData = quote.shippingAddress();
                checkoutDataObject.shipping = checkoutDataObject.shipping || {};
                checkoutDataObject.shipping.name = checkoutDataObject.shipping.name || {};
                if (shippingData.lastname) {
                    checkoutDataObject.shipping.name.full = shippingData.firstname + ' ' + shippingData.lastname;
                } else {
                    checkoutDataObject.shipping.name.full = shippingData.firstname;
                }
                checkoutDataObject.shipping.address = checkoutDataObject.shipping.address || {};
                if (shippingData.street[1]) {
                    checkoutDataObject.shipping.address.line1 = shippingData.street[0] + ' ' + shippingData.street[1];
                } else {
                    checkoutDataObject.shipping.address.line1 = shippingData.street[0];
                }
                if (shippingData.city) {
                    checkoutDataObject.shipping.address.city = shippingData.city;
                }
                if (shippingData.regionCode) {
                    checkoutDataObject.shipping.address.state = shippingData.regionCode;
                }
                if (shippingData.postcode) {
                    checkoutDataObject.shipping.address.zipcode = shippingData.postcode;
                }
                checkoutDataObject.shipping.address.country = "USA";
            },

            /**
             * Init billing data
             *
             * @param checkoutDataObject
             */
            initBillingData: function (checkoutDataObject)
            {
                var billingData = quote.billingAddress();
                checkoutDataObject.billing = checkoutDataObject.billing || {};
                checkoutDataObject.billing.name = checkoutDataObject.billing.name || {};
                if (billingData.lastname) {
                    checkoutDataObject.billing.name.full = billingData.firstname + ' ' + billingData.lastname;
                } else {
                    checkoutDataObject.billing.name.full = billingData.firstname;
                }
                checkoutDataObject.billing.address = checkoutDataObject.billing.address || {};
                if (billingData.street[1]) {
                    checkoutDataObject.billing.address.line1 = billingData.street[0] + ' ' + billingData.street[1];
                } else {
                    checkoutDataObject.billing.address.line1 = billingData.street[0];
                }
                if (billingData.city) {
                    checkoutDataObject.billing.address.city = billingData.city;
                }
                if (billingData.regionCode) {
                    checkoutDataObject.billing.address.state = billingData.regionCode;
                }
                if (billingData.postcode) {
                    checkoutDataObject.billing.address.zipcode = billingData.postcode;
                }
                checkoutDataObject.billing.address.country = "USA";
            },

            /**
             * Init quote items
             *
             * @param checkoutDataObject
             */
            initItems: function(checkoutDataObject) {
                var quoteItems = quote.getItems();
                checkoutDataObject.items = checkoutDataObject.items || [];
                for (var i = 0; i < quoteItems.length; i++) {
                    checkoutDataObject.items[i] = {};
                    checkoutDataObject.items[i].display_name = quoteItems[i].name;
                    checkoutDataObject.items[i].sku = quoteItems[i].sku;
                    checkoutDataObject.items[i].unit_price = parseInt(quoteItems[i].price * 100);
                    checkoutDataObject.items[i].qty = quoteItems[i].qty;
                    checkoutDataObject.items[i].item_image_url = quoteItems[i].thumbnail;
                    checkoutDataObject.items[i].item_url = url.build(quoteItems[i].product.request_path);
                }
            },

            /**
             * Init totals with converts to cents
             *
             * @param checkoutDataObject
             */
            initCheckoutTotals: function (checkoutDataObject) {
                var totals = quote.getTotals()();
                checkoutDataObject.total = totals.grand_total * 100;
                checkoutDataObject.shipping_amount = totals.base_shipping_amount * 100;
                checkoutDataObject.tax_amount = totals.base_tax_amount * 100;
            },

            /**
             * Init discounts metadata and others.
             *
             * @param checkoutDataObject
             */
            initAdditionalData: function (checkoutDataObject) {
                var shippingMethod = quote.shippingMethod();

                checkoutDataObject.discounts = checkoutDataObject.discounts || {};
                checkoutDataObject.metadata = checkoutDataObject.metadata || {};
                if (shippingMethod.carrier_title) {
                    checkoutDataObject.metadata.shipping_type = shippingMethod.carrier_title;
                }
            }
        });
    }
);
