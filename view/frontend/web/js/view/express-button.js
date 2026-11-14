/**
 * Affirm Express Checkout button shown in the minicart under "Go to Checkout".
 * Runs outside the checkout page, so it does not use the checkout quote model or
 * window.checkoutConfig; config comes from window.checkout.affirmExpress.
 */
define([
    'uiComponent',
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Astound_Affirm/js/model/affirm-sdk'
], function (Component, $, url, $t, customerData, affirmSdk) {
    'use strict';

    var expressConfig = (window.checkout || {}).affirmExpress;

    return Component.extend({
        defaults: {
            template: 'Astound_Affirm/minicart/express-button'
        },

        /**
         * Only shown when Express Checkout is active and the cart has items.
         *
         * @return {Boolean}
         */
        isVisible: function () {
            var cart = customerData.get('cart')();

            return !!expressConfig && !!cart.summary_count && !!cart.possible_onepage_checkout;
        },

        /**
         * Init checkout on the server with express=true, then open Affirm.
         */
        startExpressCheckout: function () {
            var self = this;

            $('body').trigger('processStart');
            $.ajax({
                url: url.build('rest/' + expressConfig.storeCode + '/V1/affirm/order/init'),
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({express: true})
            }).done(function (response) {
                var result;

                try {
                    result = typeof response === 'string' ? JSON.parse(response) : response;
                    self.openAffirm(result);
                } catch (e) {
                    self.showError();
                }
            }).fail(function () {
                self.showError();
            }).always(function () {
                $('body').trigger('processStop');
            });
        },

        /**
         * Build the express checkout object. shipping and total are intentionally omitted.
         *
         * @param {Object} result initCheckout response
         */
        openAffirm: function (result) {
            var items = customerData.get('cart')().items || [],
                checkout;

            if (!result.express_checkout || !result.order_increment_id) {
                throw new Error('Express checkout is not available');
            }
            affirmSdk.load({
                public_api_key: expressConfig.publicApiKey,
                script: expressConfig.script,
                locale: expressConfig.locale,
                country_code: expressConfig.countryCode
            });
            checkout = {
                merchant: expressConfig.merchant,
                config: {financial_product_key: null},
                items: items.map(function (item) {
                    return {
                        display_name: item.product_name,
                        sku: item.product_sku,
                        unit_price: Math.round(item.product_price_value * 100),
                        qty: item.qty,
                        item_image_url: item.product_image && item.product_image.src,
                        item_url: item.product_url
                    };
                }),
                order_id: result.order_increment_id,
                discounts: result.discounts,
                metadata: result.metadata,
                currency: result.currency,
                locale: expressConfig.locale,
                country_code: expressConfig.countryCode
            };
            if (result.financing_program) {
                checkout.financing_program = result.financing_program;
            }
            window.affirm.checkout(checkout);
            window.affirm.checkout.post();
        },

        showError: function () {
            customerData.set('messages', {
                messages: [{
                    type: 'error',
                    text: $t('We could not start Affirm Express Checkout. Please try again.')
                }]
            });
        }
    });
});
