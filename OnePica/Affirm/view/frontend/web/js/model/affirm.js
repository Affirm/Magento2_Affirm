/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(["underscore",
    "Magento_Checkout/js/model/quote",
    "mage/url",
    'Magento_Customer/js/model/customer',
    'OnePica_Affirm/js/action/verify-affirm'
], function (_, quote, url, customer, orderData) {
    'use strict';
    var configData = window.checkoutConfig.payment['affirm_gateway'];
    return {
        items: [],
        shipping: null,
        billing: null,
        config: configData.config,
        merchant: configData.merchant,
        discounts: null,
        orderId: null,
        shippingAmount: null,
        tax_amount: null,
        total: null,

        /**
         * Get checkout data
         *
         * @returns {{merchant: (*|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.OnePica_Affirm.js.model.affirm.merchant|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.OnePica_Affirm.js.model.affirm.getData.merchant|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.OnePica_Affirm.js.model.affirm.merchant|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.OnePica_Affirm.js.model.affirm.getData.merchant|checkout.merchant), config: (*|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.Magento_Shipping.js.view.checkout.shipping.shipping-policy.config|affirm2_ee_new.vendor.magento.module-shipping.view.frontend.web.js.view.checkout.shipping.shipping-policy.config|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.Magento_Shipping.js.view.checkout.shipping.shipping-policy.config|exports.file.options.config|exports.test.options.config), items: *, order_id: *, shipping_amount: (null|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.OnePica_Affirm.js.model.affirm.shippingAmount|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.OnePica_Affirm.js.model.affirm.shippingAmount|number)}}
         */
        getData: function() {
            var _self = this;
            this.prepareItems();
            this.prepareTotals();
            return {
                merchant: _self.merchant,
                config: _self.config,
                items: _self.items,
                order_id: _self.order_id,
                shipping_amount: _self.shippingAmount,
                tax_amount: _self.tax_amount,
                total: _self.total,
                shipping: _self.prepareAddress('shipping'),
                billing: _self.prepareAddress('billing')
            }
        },

        /**
         * Prepare items data
         */
        prepareItems: function() {
            var quoteItems = quote.getItems();
            for (var i=0; i < quoteItems.length; i++) {
                this.items.push({
                    display_name : quoteItems[i].name,
                    sku : quoteItems[i].sku,
                    unit_price : parseInt(quoteItems[i].price * 100),
                    qty : quoteItems[i].qty,
                    item_image_url : quoteItems[i].thumbnail,
                    item_url : url.build(quoteItems[i].product.request_path)
                });
            }
        },

        /**
         * Set order id
         *
         * @param orderId
         */
        setOrderId: function(orderId) {
            if (orderId) {
                this.orderId = orderId;
            }
        },

        /**
         * Prepare totals data
         */
        prepareTotals: function() {
            var totals = quote.getTotals()();
            if (totals.base_shipping_amount) {
                this.shippingAmount = this.convertPriceToCents(totals.base_shipping_amount);
            }
            if (totals.base_grand_total) {
                this.total = this.convertPriceToCents(totals.base_grand_total);
            }
            if (totals.base_tax_amount) {
                this.tax_amount = this.convertPriceToCents(totals.base_tax_amount);
            }
        },

        /**
         * Convert price to cents
         *
         * @param price
         * @returns {*}
         */
        convertPriceToCents: function(price) {
            if (price && price > 0) {
                return price * 100;
            }
            return 0;
        },

        /**
         * Prepare address data
         *
         * @param type
         * @returns {{}}
         */
        prepareAddress: function(type) {
            var name, address, fullname, street, result = {};
            if (type == 'shipping') {
                address = quote.shippingAddress();
            } else if (type == 'billing') {
                address = quote.billingAddress();
            }
            if (address.lastname) {
                fullname = address.firstname + ' ' + address.lastname;
            } else {
                fullname = address.firstname;
            }
            name = {
                "full": fullname
            };
            if (address.street[0]) {
                street = address.street[0];
            }
            result["address"] = {
                "line1": street,
                "city": address.city,
                "state": address.regionCode,
                "zipcode": address.postcode,
                "country": address.countryId
             };
            result["name"] = name;
            if (address.street[1]) {
                result.address.line2 = address.street[1];
            }
            if (address.telephone) {
                result.phone_number = address.telephone;
            }
            if (!customer.isLoggedIn()) {
                result.email = quote.guestEmail;
            } else if (address.email) {
                result.email = address.email;
            }
            return result;
        },

        /**
         * Specify order Data
         *
         * @param data
         */
        prepareOrderData: function(data) {
            if (data.order_increment_id !== 'undefined') {
                this.order_id = data.order_increment_id;
            }
        },

        /**
         * Add items
         *
         * @param items
         */
        addItems: function (items) {
            if (items !== 'undefined') {
                this.items = _.union(this.items, items);
            }
        }
    }
});
