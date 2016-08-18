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
    $.widget('mage.aslowasCC',{
        options: {
        },

        /**
         * Specify default price
         */
        initPrice: function() {
            var cart = customerData.get('cart');
            var priceData = cart().subtotal_excl_tax;
            this.options.aSLowAsElement = 'learn-more-mini-cart';//todo's
            if (priceData) {
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
            if (typeof affirm == "undefined") {
                $.when(aslowas.loadScript(self.options)).done(function() {
                    self.initPrice();
                });
            } else {
                self.initPrice();
            }
        }
    });
    return $.mage.aslowasCC
});
