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
define([
    "jquery",
    "mage/translate",
    "Astound_Affirm/js/model/aslowas",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Checkout/js/model/quote",
    "mage/url",
    'Magento_Customer/js/model/customer',
    "Astound_Affirm/js/model/affirm",
    'Magento_Ui/js/model/messageList'
], function ($, $t, loadScript, fullScreenLoader, quote, url, customer, affirmCheckout, Messages) {

    var options = {
        public_api_key: window.checkoutConfig.payment['affirm_gateway'].apiKeyPublic,
        script: window.checkoutConfig.payment['affirm_gateway'].script,
        locale: window.checkoutConfig.payment['affirm_gateway'].locale,
        country_code: window.checkoutConfig.payment['affirm_gateway'].countryCode,
    };

    return function(response) {
        fullScreenLoader.startLoader();
        var result = JSON.parse(response),
            giftWrapItems = result.wrapped_items,
            checkoutObj;

        affirmCheckout.prepareOrderData(result);
        if (giftWrapItems !== 'undefined') {
            affirmCheckout.addItems(giftWrapItems);
        }

        _affirm_config = {
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

        try {
            checkoutObj = affirmCheckout.getData();
            if (affirmCheckout.getFinancingProgram()) {
                checkoutObj.financing_program = affirmCheckout.getFinancingProgram();
            }
            affirm.checkout(checkoutObj);
            affirm.checkout.post();
        } catch (err) {
            Messages.addErrorMessage({
                'message': $t('We have a problem with your Affirm script loading, please verify your API URL!')}
            );
            fullScreenLoader.stopLoader();
        }
    }
});
