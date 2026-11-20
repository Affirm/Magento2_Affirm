/**
 * Loads the Affirm JS SDK on pages that do not have window.checkoutConfig
 * (e.g. the minicart). Same bootstrap snippet as action/send-to-affirm-checkout.js.
 */
define([], function () {
    'use strict';

    return {
        /**
         * @param {Object} options public_api_key, script, locale, country_code
         */
        load: function (options) {
            if (window.affirm && window.affirm.checkout && window.__affirmExpressLoaded) {
                return;
            }
            window.__affirmExpressLoaded = true;
            window._affirm_config = {
                public_api_key: options.public_api_key,
                script: options.script,
                locale: options.locale,
                country_code: options.country_code
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
                b[d] = l(b, d, 'set');
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
                for (c = 'set add save post open empty reset on off trigger ready setProduct'.split(' ');
                     a < c.length; a++) {
                    f[c[a]] = l(b, d, c[a]);
                }
                a = 0;
                for (c = ['get', 'token', 'url', 'items']; a < c.length; a++) {
                    f[c[a]] = function () {};
                }
                k.async = !0;
                k.src = g[e];
                p.parentNode.insertBefore(k, p);
                delete g[e];
                f(g);
                m[n] = b;
            })(window, window._affirm_config, 'affirm', 'checkout', 'ui', 'script', 'ready', 'jsReady');
        }
    };
});
