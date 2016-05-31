/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "jquery/ui",
    "Astound_Affirm/js/model/aslowas"
], function ($, $t) {
    "use strict"

    $.widget('mage.affirmWidget',{
        options: {},
        _create: function() {
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
            })(window, this.options, "affirm", "checkout", "ui", "script", "ready");
        }
    });
    return $.mage.affirmWidget
});
