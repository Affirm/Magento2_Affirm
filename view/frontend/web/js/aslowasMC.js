/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas",
    "Magento_Customer/js/customer-data"
], function ($, $t, aslowas, customerData) {
    "use strict"

    var self;
    var miniCart = $('[data-block=\'minicart\']');

    $.widget('mage.aslowasMC',{
        options: {
        },
        aSLowAsElement: 'learn-more-mini-cart',

        /**
         * Specify default price
         */
        initPrice: function() {
            var priceData, cart = customerData.get('cart');
            if (!this.options.display_cart_subtotal_excl_tax && this.options.display_cart_subtotal_incl_tax) {
                priceData = cart().subtotal_incl_tax;
            } else {
                priceData = cart().subtotal_excl_tax;
            }
            if (priceData) {
                this.options.aSLowAsElement = this.aSLowAsElement;
                aslowas.process(priceData, this.options);
            }
        },

        /**
         * Create as low as widget
         *
         * @private
         */
        _create: function() {
            self = this;

            self.options = {
                element_id: (window.checkout && window.checkout.element_id) ? window.checkout.element_id : null,
                promo_id: (window.checkout && window.checkout.promo_id) ? window.checkout.promo_id : null,
                color_id: (window.checkout && window.checkout.color_id) ? window.checkout.color_id : null,
                asLowAsActiveMiniCart: (window.checkout && window.checkout.asLowAsActiveMiniCart) ? window.checkout.asLowAsActiveMiniCart : null,
                apr: (window.checkout && window.checkout.apr) ? window.checkout.apr : null,
                months: (window.checkout && window.checkout.months) ? window.checkout.months : null,
                logo: (window.checkout && window.checkout.logo) ? window.checkout.logo : null,
                script: (window.checkout && window.checkout.script) ? window.checkout.script : null,
                public_api_key: (window.checkout && window.checkout.public_api_key) ? window.checkout.public_api_key : null,
                country_code: (window.checkout && window.checkout.country_code) ? window.checkout.country_code : null,
                locale: (window.checkout && window.checkout.locale) ? window.checkout.locale : null,
                min_order_total: (window.checkout && window.checkout.min_order_total) ? window.checkout.min_order_total : null,
                max_order_total: (window.checkout && window.checkout.max_order_total) ? window.checkout.max_order_total : null,
                currency_rate: (window.checkout && window.checkout.currency_rate) ? window.checkout.currency_rate : null,
                display_cart_subtotal_incl_tax: (window.checkout && window.checkout.display_cart_subtotal_incl_tax) ? window.checkout.display_cart_subtotal_incl_tax : null,
                display_cart_subtotal_excl_tax: (window.checkout && window.checkout.display_cart_subtotal_excl_tax) ? window.checkout.display_cart_subtotal_excl_tax : null,
            };

            if (!self.options.asLowAsActiveMiniCart) {
                return;
            }
            if (typeof affirm == "undefined") {
                $.when(aslowas.loadScript(self.options)).done(function() {
                    self.initPrice();
                });
            } else {
                self.initPrice();
            }

            miniCart.on('contentUpdated', function () {
                self.initPrice();
            });
        }
    });
    return $.mage.aslowasMC
});
