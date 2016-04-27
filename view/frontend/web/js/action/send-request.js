/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery', 'mage/url'], function ($, urlBuilder) {
    'use strict';

    return {
        /**
         * Perform asynchronous POST request to server.
         * @param {String} url
         * @param {String} data
         * @param {Boolean} global
         * @param {String} contentType
         * @returns {Deferred}
         */
        post: function (url, data, global, contentType) {
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'POST',
                data: data,
                global: global,
                contentType: contentType,
                async: false
            });
        }
    };
});
