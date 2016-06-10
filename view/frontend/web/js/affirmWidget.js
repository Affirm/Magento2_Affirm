/**
 * Copyright © 2016 Astound. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint jquery:true*/
define([
    "jquery",
    "Astound_Affirm/js/model/aslowas",
    "jquery/ui"
], function ($, aslowas) {

    "use strict"

    $.widget('mage.affirmWidget', {

        /**
         * Widget options
         */
        options: {},

        /**
         * Init widget method
         *
         * @private
         */
        _create: function() {
            if (typeof affirm == "undefined") {
                aslowas.loadScript(this.options);
            }
        }
    });

    return $.mage.affirmWidget
});
