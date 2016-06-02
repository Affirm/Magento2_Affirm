/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas",
    'Magento_Checkout/js/model/quote',
    "jquery/ui"
], function ($, $t, aslowas, quote) {
    "use strict"

    var self;
    $.widget('mage.aslowasCC',{
        options: {
        },

        /**
         * Specify default price
         */
        initPrice: function() {
            var price = quote.getTotals()();
            if (price && price.base_grand_total) {
                aslowas.process(price.base_grand_total, this.options);
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
