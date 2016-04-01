/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "affirmScript",
    "jquery/ui"
], function ($, $t) {
    "use strict"

    $.widget('mage.affirmCheckout',{
        _create: function() {
            affirm.checkout(this.options);
        }
    });
    return $.mage.affirmCheckout
});
