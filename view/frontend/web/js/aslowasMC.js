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
        }
    });
    return $.mage.aslowasMC
});
