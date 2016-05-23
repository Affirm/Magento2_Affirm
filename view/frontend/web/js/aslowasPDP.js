/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas",
    "jquery/ui"
], function ($, $t, aslowas) {
    "use strict"
    var self,
        selector = '.price-box';

    $.widget('mage.aslowas',{
        options: {
        },

        /**
         * Specify default price
         */
        initPrice: function() {
            var price = $(this.element)
                .parent()
                .find('.price-final_price .price')
                .text();
            if (price) {
                aslowas.process(price, this.options);
            }
        },

        /**
         * Create as low as widget
         *
         * @private
         */
        _create: function() {
            self = this;
            var priceBox = $(selector);
            $.when(aslowas.loadScript(self.options)).done(function() {
                self.initPrice();
                priceBox.on('updatePrice', self.updatePriceHandler);
            });
        },

        /**
         * Handle update price event
         *
         * @param event
         */
        updatePriceHandler: function(event) {
            var el = $(event.currentTarget),
                price,
                priceInfo = $(el).parents('.product-info-main').get(0),
                currentElement;

            if (priceInfo) {
                //get first element from the array
                currentElement = $(self.element).get(0);
                if ($.contains(priceInfo, currentElement)) {
                    price = el[0].innerText;
                    aslowas.process(price);
                }
            }
        }
    });
    return $.mage.aslowas
});
