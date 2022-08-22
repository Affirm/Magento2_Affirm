/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "underscore",
    "Magento_Checkout/js/model/quote",
    "mage/url",
    'Magento_Customer/js/model/customer'
], function (_, quote, url, customer) {
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
        currency: configData.currency,
        locale: configData.locale,
        country_code: configData.CountryCode,

        /**
         * Get checkout data
         *
         * @returns {{merchant: (*|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.Astound_Affirm.js.model.affirm.merchant|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.Astound_Affirm.js.model.affirm.getData.merchant|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.Astound_Affirm.js.model.affirm.merchant|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.Astound_Affirm.js.model.affirm.getData.merchant|checkout.merchant), config: (*|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.Magento_Shipping.js.view.checkout.shipping.shipping-policy.config|affirm2_ee_new.vendor.magento.module-shipping.view.frontend.web.js.view.checkout.shipping.shipping-policy.config|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.Magento_Shipping.js.view.checkout.shipping.shipping-policy.config|exports.file.options.config|exports.test.options.config), items: *, order_id: *, shipping_amount: (null|affirm2_ee_new.pub.static.frontend.Magento.luma.en_US.Astound_Affirm.js.model.affirm.shippingAmount|affirm2_ee_new.pub.static.frontend.Magento.blank.en_US.Astound_Affirm.js.model.affirm.shippingAmount|number)}}
         */
        getData: function() {
            var _self = this;
            this.prepareItems();
            this.prepareTotals();
            this.initMetadata();
            return {
                merchant: _self.merchant,
                config: _self.config,
                items: _self.items,
                order_id: _self.order_id,
                shipping_amount: _self.shippingAmount,
                tax_amount: _self.tax_amount,
                total: _self.total,
                shipping: _self.prepareAddress('shipping'),
                billing: _self.prepareAddress('billing'),
                discounts: _self.discounts,
                metadata: _self.metadata,
                currency: _self.currency,
                locale: _self.locale,
                country_code: _self.country_code
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
                    item_url : (quoteItems[i].product.request_path) ?
                        url.build(quoteItems[i].product.request_path) : quoteItems[i].thumbnail
                });
            }
        },

        /**
         * Init metadata
         */
        initMetadata: function() {
            if (!this.metadata.shipping_type && quote.shippingMethod()) {
                this.metadata.shipping_type =
                    quote.shippingMethod().carrier_title + ' - ' + quote.shippingMethod().method_title;
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
            this.shippingAmount = this.convertPriceToCents(totals.shipping_amount);
            this.total = this.convertPriceToCents(totals.grand_total);
            this.tax_amount = this.convertPriceToCents(totals.tax_amount);
        },

        /**
         * Convert price to cents
         *
         * @param price
         * @returns {*}
         */
        convertPriceToCents: function(price) {
            if (price && price > 0) {
                price = Math.round(price*100);
                return price;
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
            var name, address, fullname, firstName, lastName, street, result = {};
            if (type == 'shipping') {
                address = quote.shippingAddress();
            } else if (type == 'billing') {
                address = quote.billingAddress();
            }
            if (address.lastname ? address.lastname : this.address[type].name.last) {
                firstName = address.firstname ? address.firstname : this.address[type].name.first;
                lastName = address.lastname ? address.lastname : this.address[type].name.last;
                fullname = firstName + ' ' + lastName;
            } else {
                fullname = address.firstname ? address.firstname : this.address[type].name.first;
            }
            name = {
                "full": fullname
            };
            if (address.street !== undefined) {
                street = address.street[0];
            } else {
                street = this.address[type].address.line[0];
            }
            result["address"] = {
                "street1": street,
                "city": address.city ? address.city : this.address[type].address.city,
                "region1_code": address.regionCode ? address.regionCode : this.address[type].address.state,
                "postal_code": address.postcode ? address.postcode : this.address[type].address.postcode,
                "country": address.countryId ? address.countryId : this.address[type].address.country
             };
            result["name"] = name;
            if (address.street !== undefined) {
                if (address.street[1]) {
                    result.address.street2 = address.street[1];
                }
            } else if(this.address[type].address.line[1]) {
                result.address.street2 = this.address[type].address.line[1]
            }
            if (address.telephone) {
                result.phone_number = address.telephone;
            }
            if (!customer.isLoggedIn()) {
                result.email = quote.guestEmail;
            } else if (customer.customerData.email) {
                result.email = customer.customerData.email;
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
            if (data.discounts) {
                this.setDiscounts(data.discounts);
            }
            if (data.metadata) {
                this.setMetadata(data.metadata);
            }
            if (data.financing_program) {
                this.setFinancingProgram(data.financing_program);
            }

            if (data.product_types) {
                this.setProductTypes(data.product_types);
            }

            if (data.address) {
                this.setAddress(data.address);
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
        },

        /**
         * Specify discount
         *
         * @param discounts
         */
        setDiscounts: function(discounts) {
            if (discounts) {
                this.discounts = discounts;
            }
        },

        /**
         * Specify metadata
         *
         * @param metadata
         */
        setMetadata: function(metadata) {
            if (metadata) {
                this.metadata = metadata;
            }
        },

        /**
         * Specify financing program
         *
         * @param financing_program
         */
        setFinancingProgram: function(financing_program) {
            if (financing_program) {
                this.financing_program = financing_program;
            }
        },

        /**
         * list of productTypes
         *
         * @param product Type Array
         */
        setProductTypes: function(product_types) {
            if (product_types) {
                this.product_types = product_types;
            }
        },

        /**
         * Specify address
         *
         * @param quote address
         */
        setAddress: function(address) {
            if (address) {
                this.address = address;
            }
        },

        /**
         * Get specified financing program
         *
         * @return string
         */
        getFinancingProgram: function() {
            return this.financing_program;
        }
    }
});
