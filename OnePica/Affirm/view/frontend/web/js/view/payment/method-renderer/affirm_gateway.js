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
        'mage/url'
    ],
    function (Component, AffirmCheckout, quote, additionalValidator, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnePica_Affirm/payment/form',
                transactionResult: ''
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
             * After place order handler
             */
            afterPlaceOrder: function () {
                var totals = quote.getTotals()(),
                    checkoutData = {},
                    billingData = quote.billingAddress(),
                    shippingData = quote.shippingAddress(),
                    shippingMethod = quote.shippingMethod(),
                    quoteItems = quote.getItems();

                // Create checkout data object
                checkoutData.merchant = {};
                checkoutData.merchant.user_confirmation_url = window.checkoutConfig.payment['affirm_gateway'].merchant.confirmationUrl;
                checkoutData.merchant.user_cancel_url = window.checkoutConfig.payment['affirm_gateway'].merchant.cancelUrl;

                checkoutData.config = {};
                checkoutData.config.financial_product_key =  window.checkoutConfig.payment['affirm_gateway'].config.financialKey;

                checkoutData.shipping = {};
                checkoutData.shipping.name = {};
                checkoutData.shipping.name.full = shippingData.firstname + ' ' + shippingData.lastname;

                checkoutData.shipping.address = {};
                checkoutData.shipping.address.line1 = shippingData.street[0] + ' ' + shippingData.street[1];

                checkoutData.shipping.address.city = shippingData.city;
                checkoutData.shipping.address.state = shippingData.regionCode;
                checkoutData.shipping.address.zipcode = shippingData.postcode;
                checkoutData.shipping.address.country = "USA";

                //Specify billing data
                checkoutData.billing = {};
                checkoutData.billing.name = {};
                checkoutData.billing.name.full = billingData.firstname + ' ' + billingData.lastname;
                checkoutData.billing.address = {};
                checkoutData.billing.address.line1 = billingData.street[0] + ' ' + billingData.street[1];

                checkoutData.billing.address.city = billingData.city;
                checkoutData.billing.address.state = billingData.regionCode;
                checkoutData.billing.address.zipcode = billingData.postcode;
                checkoutData.billing.address.country = "USA";
                checkoutData.items = [];

                for (var i = 0; i < quoteItems.length; i++) {
                    checkoutData.items[i] = {};
                    checkoutData.items[i].display_name = quoteItems[i].name;
                    checkoutData.items[i].sku = quoteItems[i].sku;
                    checkoutData.items[i].unit_price = parseInt(quoteItems[i].price * 100);
                    checkoutData.items[i].qty = quoteItems[i].qty;
                    checkoutData.items[i].item_image_url = quoteItems[i].thumbnail;
                    checkoutData.items[i].item_url = url.build(quoteItems[i].product.request_path);
                }
                checkoutData.discounts = {};
                checkoutData.metadata = {};

                checkoutData.metadata.shipping_type = shippingMethod.carrier_title;
                checkoutData.order_id = "#";
                checkoutData.total = totals.grand_total * 100;
                checkoutData.shipping_amount = totals.base_shipping_amount;
                checkoutData.tax_amount = totals.base_tax_amount;

                if (additionalValidator.validate()) {
                    // setup and configure checkout
                    affirm.checkout(checkoutData);
                    affirm.checkout.post();
                }
            }
        });
    }
);
