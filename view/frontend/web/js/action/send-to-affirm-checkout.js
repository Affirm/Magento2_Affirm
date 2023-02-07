/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
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
