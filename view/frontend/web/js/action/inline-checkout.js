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
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
], function ($,storage, urlBuilder) {
    'use strict';
    var configData = window.checkoutConfig.payment['affirm_gateway'];
    var options = {
        public_api_key: window.checkoutConfig.payment['affirm_gateway'].apiKeyPublic,
        script: window.checkoutConfig.payment['affirm_gateway'].script,
        locale: window.checkoutConfig.payment['affirm_gateway'].locale,
        country_code: window.checkoutConfig.payment['affirm_gateway'].countryCode,
    };

    /**
     * The real Affirm SDK and its own bootstrap stub both live at window.affirm, so
     * existence alone can't tell them apart. The stub's method list never includes
     * "inline", so a real .checkout.inline function is what marks the SDK as loaded.
     */
    function isAffirmSdkReady() {
        return !!(window.affirm && window.affirm.checkout && typeof window.affirm.checkout.inline === 'function');
    }

    /**
     * Affirm's standard async-loader snippet. Only runs when the SDK isn't already
     * loaded - running it again after a successful load would overwrite the real
     * affirm.checkout with a fresh stub (which has no .inline method) and re-insert
     * another <script src="affirm.js"> into the page.
     */
    function loadAffirmSdk(affirmConfig) {
        if (isAffirmSdkReady()) {
            return;
        }
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
        })(window, affirmConfig, "affirm", "checkout", "ui", "script", "ready", "jsReady");
    }

    /**
     * Applies the latest checkout data and (re)renders the inline widget, deferred
     * until the real SDK has replaced the bootstrap stub.
     */
    function renderInlineCheckout(response) {
        affirm.ui.ready(function() {
            affirm.checkout(JSON.parse(response))
            affirm.checkout.inline({
                merchant: {
                    inline_container: "affirm-inline-checkout"
                },
            });
        })
    }

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
                    loadAffirmSdk(_affirm_config);
                    renderInlineCheckout(response);
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        },

        updateInlineCheckout : function(){
            let serviceUrl = urlBuilder.createUrl('/affirm/checkout/inline', {}), result;
            storage.get(
                serviceUrl
            ).done(
                function(response) {
                    var _affirm_config = {
                        public_api_key: options.public_api_key,
                        script: options.script,
                        locale: options.locale,
                        country_code: options.country_code,
                    };
                    loadAffirmSdk(_affirm_config);
                    renderInlineCheckout(response);
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        }
    }
})
