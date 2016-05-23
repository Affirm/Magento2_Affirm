/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "mage/translate"
],
    function ($, $t) {
        'use strict';

        var self;
        return {
            options: {},

            /**
             * Process price
             *
             * @param price
             */
            process: function(price, options) {
                self = this;
                var formatted, priceInt, optionsPrice;
                formatted = Number(price.replace(/[^0-9\.]+/g,""));
                priceInt = formatted * 100;

                if (options) {
                    self.options = options;
                }
                /**
                 * Specify options
                 *
                 * @type {{apr: (*|apr), months: (*|months|c.months|.step.months|gc.months|._data.months), amount: number}}
                 */
                optionsPrice = {
                    apr: self.options.apr,
                    months: self.options.months,
                    amount: priceInt
                };
                if (priceInt > 5000) {
                    self.processAsLowAs(optionsPrice);
                }
            },

            /**
             * Process as low as functionality
             *
             * @param options
             */
            processAsLowAs: function(options) {
                var self = this;
                affirm.ui.ready(function() {
                    if (options.amount) {
                        self.updateAffirmAsLowAs(options.amount);
                        affirm.ui.payments.get_estimate(options, self.handleEstimateResponse);
                    }
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
                    content = $t("Starting at $") + dollars + $t(" a month with") + ' ' + self.options.logo + ' ' + $t("Learn more.");
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
            loadScript: function(options) {
                "use strict";
                var pubKey = options.public_api_key,
                    script = options.script,
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
        }
    });
