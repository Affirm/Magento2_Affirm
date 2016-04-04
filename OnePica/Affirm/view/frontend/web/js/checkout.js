/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "OnePica_Affirm/js/affirm",
    "jquery/ui"
], function ($, $t, loadScript) {
    "use strict"
    $.widget('mage.affirmCheckout', {
        _create: function() {
            var checkout = {},
                publicKey = this.options.public_key,
                scriptUrl = this.options.script;

            // Load affirm js script before using affirm js object
            loadScript(publicKey, scriptUrl);

            if (this.options.merchant) {
                checkout.merchant = this.options.merchant;
            }
            if (this.options.config) {
                checkout.config = this.options.config;
            }
            if (this.options.shipping) {
                checkout.shipping = this.options.shipping;
            }
            if (this.options.billing) {
                checkout.billing = this.options.billing;
            }
            if (this.options.metadata) {
                checkout.metadata = this.options.metadata;
            }
            if (this.options.items) {
                checkout.items = this.options.items;
            }
            if (this.options.discounts) {
                checkout.discounts = this.options.discounts;
            }
            if (this.options.order_id) {
                checkout.order_id = this.options.order_id;
            }

            if (this.options.tax_amount) {
                checkout.tax_amount = this.options.tax_amount * 100;
            }

            if (this.options.shipping_amount) {
                checkout.shipping_amount = this.options.shipping_amount;
            }
            if (this.options.total) {
                checkout.total = this.options.total;
            }
            if (this.options.total) {
                checkout.total = this.options.total;
            }
            affirm.checkout(checkout);
            affirm.checkout.post();
        }
    });
    return $.mage.affirmCheckout
});
