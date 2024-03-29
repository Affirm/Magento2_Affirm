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
    $.widget('mage.aslowasPDP',{
        options: {
        },
        /**
         * Specify default price
         */
        initPrice: function() {
            var price = $(this.element)
                .parent()
                .find("[data-price-type='finalPrice']")
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
            if (typeof affirm == "undefined") {
                $.when(aslowas.loadScript(self.options)).done(function() {
                    self.initPrice();
                    priceBox.on('updatePrice', self.updatePriceHandler);
                });
            } else {
                self.initPrice();
                priceBox.on('updatePrice', self.updatePriceHandler);
            }
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
                currentElement;

            if (priceInfo) {
                //get first element from the array
                currentElement = $(self.element).get(0);
                if ($.contains(priceInfo, currentElement)) {
                    price = $(el[0]).find("[data-price-type='finalPrice']").text();
                    aslowas.process(price, self.options);
                }
            }

            aslowas.processBackordersVisibility(self.options.backorders_options);
        }
    });
    return $.mage.aslowasPDP
});
