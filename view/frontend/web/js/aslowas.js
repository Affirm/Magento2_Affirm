/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    "use strict"
    var self,
        selector = '.price-box';

    $.widget('mage.aslowas',{
        options: {
        },

        initPrice: function() {
            var price = $(this.element).parent().find('.price-final_price .price').text();
            self.process(price);
        },

        /**
         * Handle update price event
         *
         * @param event
         */
        updatePriceHandler: function(event) {
            var el = $(event.currentTarget), price;
            if ($.contains($(el).parents('.product-info-main')[0], self.element[0])) {
                price = el[0].innerText;
                self.process(price);
            }
        },

        /**
         * Process price
         *
         * @param price
         */
        process: function(price) {
            var options, formatted, priceInt;
            formatted = Number(price.replace(/[^0-9\.]+/g,""));
            priceInt = formatted * 100;

            /**
             * Specify options
             *
             * @type {{apr: (*|apr), months: (*|months|c.months|.step.months|gc.months|._data.months), amount: number}}
             */
            options = {
                apr: self.options.apr,
                months: self.options.months,
                amount: priceInt
            };
            if (priceInt > 5000) {
                self.processAslowAs(options);
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
            $.when(self._loadScript()).done(function() {
                self.initPrice();
                priceBox.on('updatePrice', self.updatePriceHandler);
            });
        },

        /**
         * Process as low as functionality
         *
         * @param options
         */
        processAslowAs: function(options) {
            affirm.ui.ready(function() {
                self.updateAffirmAsLowAs(options.amount);
                affirm.ui.payments.get_estimate(options, self.handleEstimateResponse);
            });
        },

        /**
         * Update affirm as low as
         *
         * @param c This is amount in cents
         */
        updateAffirmAsLowAs: function(c) {
            if ((c == null) || (c > 175000) || (c < 5000)) {
                return;
            }
            if (c) {
                this.options.amount = c;
            }
        },

        /**
         * Handle estimate response
         */
        handleEstimateResponse: function(payment_estimate) {
            var dollars = payment_estimate.payment_string, element,
                content = $t("Starting at $") + dollars + $t(" a month with")+ ' ' + self.options.logo + ' ' + $t("Learn more.");
            element = document.getElementById('learn-more');
            element.innerHTML = content;
            if (payment_estimate && payment_estimate.open_modal) {
                element.onclick = payment_estimate.open_modal;
            }
            element.style.visibility = "visible";
        },

        /**
         * Load affirm script
         *
         * @private
         */
        _loadScript: function() {
            "use strict";
            var pubKey = this.options.public_api_key,
                script = this.options.script,
                _affirm_config = {
                    public_api_key: pubKey, /* Use the PUBLIC API KEY Affirm sent you. */
                    script: script
                };
            (function(l, g, m, e, a, f, b) {
                var d, c = l[m] || {},
                    h = document.createElement(f),
                    n = document.getElementsByTagName(f)[0],
                    k = function(a, b, c) {
                        return function() {
                            a[b]._.push([c, arguments])
                        }
                    };
                c[e] = k(c, e, "set");
                d = c[e];
                c[a] = {};
                c[a]._ = [];
                d._ = [];
                c[a][b] = k(c, a, b);
                a = 0;
                for (b = "set add save post open empty reset on off trigger ready setProduct".split(" "); a < b.length; a++) d[b[a]] = k(c, e, b[a]);
                a = 0;
                for (b = ["get", "token", "url", "items"]; a < b.length; a++) d[b[a]] = function() {};
                h.async = !0;
                h.src = g[f];
                n.parentNode.insertBefore(h, n);
                delete g[f];
                d(g);
                l[m] = c
            })(window, _affirm_config, "affirm", "checkout", "ui", "script", "ready");
        }
    });
    return $.mage.aslowas
});
