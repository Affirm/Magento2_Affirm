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
            if (price && price.grand_total && parseFloat(price.grand_total) > 0) {
                result = parseFloat(price.grand_total).toFixed(2);
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
