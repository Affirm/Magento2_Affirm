/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas"
], function ($, $t, aslowas) {

    "use strict"

    var self,
        selector = '.price-box';
    $.widget('mage.aslowasPLP',{
        options: {
        },
        /**
         * Specify default price
         */
        initPrices: function() {
            var elements = $(document)
                    .find("[data-price-type='finalPrice'], [data-price-type='minPrice'], [data-price-type='starting-price']"),
                price,
                options,
                element_id,
                element_als;

            $.each(elements, function (key, element) {
                price = $(element).text();
                if($(element).attr("data-price-type") == "finalPrice") {
                    if($(element).parents('.special-price').length > 0) {
                        element_id = 'as_low_as_plp_' + $(element).parent().parent().parent().attr('data-product-id');
                    } else {
                        element_id = 'as_low_as_plp_' + $(element).parent().parent().attr('data-product-id');
                    }
                } else {
                    element_id = 'as_low_as_plp_' + $(element).parent().parent().parent().attr('data-product-id');
                }
                element_als = document.getElementById(element_id);
                if (price && element_als) {
                    options = self.clone(self.options);
                    options.element_id = element_id;
                    aslowas.process(price, options);
                }
            });
        },

        /**
         * Create as low as widget
         *
         * @private
         */
        _create: function() {
            self = this;
            var priceBox = $(selector);
            if (typeof affirm == "undefined") {
                $.when(aslowas.loadScript(self.options)).done(function() {
                    self.initPrices();
                    priceBox.on('updatePrice', self.updatePriceHandler);
                });
            } else {
                self.initPrices();
                priceBox.on('updatePrice', self.updatePriceHandler);
            }
        },

        /**
         * Clone an object
         *
         * @param event
         */
        clone: function(obj) {
            if (null == obj || "object" != typeof obj) return obj;
            var copy = obj.constructor();
            for (var attr in obj) {
                if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
            }
            return copy;
        },

        /**
         * Handle update price event
         *
         * @param event
         */
        updatePriceHandler: function(event) {
            var el = $(event.currentTarget),
                price,
                priceInfo = $(el).parents(self.options.selector).get(0),
                currentElement,
                options;

            if (priceInfo) {
                //get first element from the array
                currentElement = $(self.element).get(0);
                if ($.contains(priceInfo, currentElement)) {
                    price = $(el[0]).find("[data-price-type='finalPrice'], [data-price-type='minPrice']").text();
                    options = this.options;
                    options.element_id = 'as_low_as_plp_' + $(el[0]).find("[data-price-type='finalPrice'], [data-price-type='minPrice'], [data-price-type='starting-price']").parent().parent().parent().attr('data-product-id');
                    aslowas.process(price, options);
                }
            }

            aslowas.processBackordersVisibility(self.options.backorders_options);
        }
    });
    return $.mage.aslowasPLP
});
