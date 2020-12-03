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
    "jquery",
    "mage/translate"
],
    function ($, $t) {
        'use strict';

        var self;
        return {
            options: {},

            /**
             * Load affirm script
             *
             * @private
             */
            loadScript: function (options) {
                "use strict";
                var pubKey = options.public_api_key,
                    script = options.script,
                    _affirm_config = {
                        public_api_key: pubKey, /* Use the PUBLIC API KEY Affirm sent you. */
                        script: script,
                    };
                (function (l, g, m, e, a, f, b) {
                    var d, c = l[m] || {},
                        h = document.createElement(f),
                        n = document.getElementsByTagName(f)[0],
                        k = function (a, b, c) {
                            return function () {
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
                    for (b = ["get", "token", "url", "items"]; a < b.length; a++) d[b[a]] = function () {
                    };
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
