/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint jquery:true*/
define([
    "jquery",
    "Astound_Affirm/js/model/aslowas"
], function ($, aslowas) {

    "use strict"

    var self;
    $.widget('mage.affirmWidget', {

        /**
         * Widget options
         */
        options: {},

        /**
         * Create affirm widget
         *
         * @private
         */

        _create: function() {
            self = this;
            var priceBox = $('.price-box');
            if (typeof affirm == "undefined") {
                $.when(aslowas.loadScript(self.options)).done(function() {
                    if (priceBox.length && self.options.backorders_options !== 'undefined') {
                        priceBox.on('updatePrice', self.updatePriceHandler);
                    }
                });
            } else if (priceBox.length && self.options.backorders_options !== 'undefined') {
                priceBox.on('updatePrice', self.updatePriceHandler);
            }
        },

        /**
         * Handle update price event
         *
         * @param event
         */
        updatePriceHandler: function(event) {
            aslowas.processBackordersVisibility(self.options.backorders_options);
        }
    });

    return $.mage.affirmWidget
});
