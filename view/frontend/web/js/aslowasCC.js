/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas",
    "Magento_Checkout/js/model/quote"
], function ($, $t, aslowas, quote) {
    "use strict"

    var self;
    $.widget('mage.aslowasCC',{
        options: {
        },

        /**
         * Specify default price
         */
        initPrice: function(newValue) {
            var price = quote.getTotals()(), result;
            if (newValue) {
                price = newValue;
            }
            if (price && price.grand_total) {
                result = price.grand_total.toString();
                aslowas.process(result, this.options);
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
            quote.totals.subscribe(function(newValue) {
                self.initPrice(newValue);
            });
        }
    });
    return $.mage.aslowasCC
});
