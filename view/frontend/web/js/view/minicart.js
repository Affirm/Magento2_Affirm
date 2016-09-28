/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'Astound_Affirm/js/aslowasMC',
    'sidebar',
], function (Component, customerData, $, ko, _, aslowasMC) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');
    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });

    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__(
                'Are you sure you would like to remove this item from the shopping cart?'
            )
        });
    }

    return Component.extend({
        asLowAsMCOptions: {
            asLowAsActiveMiniCart: window.checkout.asLowAsActiveMiniCart,
            apr: window.checkout.apr,
            months: window.checkout.months,
            logo: window.checkout.logo,
            script: window.checkout.script,
            public_api_key: window.checkout.public_api_key,
            min_order_total: window.checkout.min_order_total,
            max_order_total: window.checkout.max_order_total,
            currency_rate: window.checkout.currency_rate,
            display_cart_subtotal_incl_tax: window.checkout.display_cart_subtotal_incl_tax,
            display_cart_subtotal_excl_tax: window.checkout.display_cart_subtotal_excl_tax
        },
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        cart: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);
            $('[data-block="minicart"]').on('contentLoading', function (event) {
                addToCartCalls++;
                self.isLoading(true);
            });

            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var minicart = $('[data-block="minicart"]');
            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },

        /**
         * Call As Low As mini cart widget
         *
         * @returns bool
         */
        initAsLowAsMiniCart: function() {
            var cart = customerData.get('cart');
            if (cart().allow_affirm_quote_aslowas) {
                aslowasMC(this.asLowAsMCOptions);
                return true;
            }
            return false;
        }
    });
});
