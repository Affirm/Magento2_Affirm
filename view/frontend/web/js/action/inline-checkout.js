/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
], function ($,storage, urlBuilder) {
    'use strict';
    var configData = window.checkoutConfig.payment['affirm_gateway'];
    var checkoutObject
    var initAffirmInline = true
    var options = {
        public_api_key: window.checkoutConfig.payment['affirm_gateway'].apiKeyPublic,
        script: window.checkoutConfig.payment['affirm_gateway'].script,
        locale: window.checkoutConfig.payment['affirm_gateway'].locale,
        country_code: window.checkoutConfig.payment['affirm_gateway'].countryCode,
    };

    return {
        inlineCheckout: function(){
            let serviceUrl = urlBuilder.createUrl('/affirm/checkout/inline', {}), result;
            storage.get(
                serviceUrl
            ).done(
                function(response) {
                    var _affirm_config = {
                        public_api_key: options.public_api_key, /* Use the PUBLIC API KEY Affirm sent you. */
                        script: options.script,
                        locale: options.locale,
                        country_code: options.country_code,
                    };
                    (function (m, g, n, d, a, e, h, c) {
                        var b = m[n] || {},
                            k = document.createElement(e),
                            p = document.getElementsByTagName(e)[0],
                            l = function (a, b, c) {
                                return function () {
                                    a[b]._.push([c, arguments]);
                                };
                            };
                        b[d] = l(b, d, "set");
                        var f = b[d];
                        b[a] = {};
                        b[a]._ = [];
                        f._ = [];
                        b._ = [];
                        b[a][h] = l(b, a, h);
                        b[c] = function () {
                            b._.push([h, arguments]);
                        };
                        a = 0;
                        for (c = "set add save post open empty reset on off trigger ready setProduct".split(" "); a < c.length; a++) f[c[a]] = l(b, d, c[a]);
                        a = 0;
                        for (c = ["get", "token", "url", "items"]; a < c.length; a++) f[c[a]] = function () {};
                        k.async = !0;
                        k.src = g[e];
                        p.parentNode.insertBefore(k, p);
                        delete g[e];
                        f(g);
                        m[n] = b;
                    })(window, _affirm_config, "affirm", "checkout", "ui", "script", "ready", "jsReady");
                    if(!(checkoutObject == response)) {
                        affirm.ui.ready(function() {
                            affirm.checkout(JSON.parse(response))
                            affirm.checkout.inline({
                                merchant: {
                                    inline_container: "affirm-inline-checkout"
                                },
                            });
                        })
                        initAffirmInline = false
                    } else {
                        affirm.checkout.inline({
                            container: "affirm-inline-checkout",
                            data: JSON.parse(response),
                        });
                    }
                    checkoutObject = response
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        },

        updateInlineCheckout : function(){
            var _self = this;
            let serviceUrl = urlBuilder.createUrl('/affirm/checkout/inline', {}), result;
            storage.get(
                serviceUrl
            ).done(
                function(response) {
                    setTimeout(function(){
                        $('.action-apply').click(function(){
                            _self.updateInlineCheckout()
                        })
                        $('.action-cancel').click(function(){
                            _self.updateInlineCheckout()
                        })
                        $('.action-update').click(function(){
                            _self.updateInlineCheckout()
                        })
                    },
                    3000);
                    affirm.checkout.inline({
                        container: "affirm-inline-checkout",
                        data: JSON.parse(response),
                    });
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        }
    }
})
