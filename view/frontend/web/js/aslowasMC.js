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
                element_id: window.checkout.element_id,
                promo_id: window.checkout.promo_id,
                color_id: window.checkout.color_id,
                asLowAsActiveMiniCart: window.checkout.asLowAsActiveMiniCart,
                apr: window.checkout.apr,
                months: window.checkout.months,
                logo: window.checkout.logo,
                script: window.checkout.script,
                public_api_key: window.checkout.public_api_key,
                min_order_total: window.checkout.min_order_total,
                max_order_total: window.checkout.max_order_total,
                currency_rate: window.checkout.currency_rate,
                display_cart_subtotal_incl_tax: window.checkout.display_cart_subtotal_incl_tax,
                display_cart_subtotal_excl_tax: window.checkout.display_cart_subtotal_excl_tax
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
