/*
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
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
