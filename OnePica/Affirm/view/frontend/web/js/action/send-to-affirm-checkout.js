/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "OnePica_Affirm/js/affirm",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Checkout/js/model/quote",
    "mage/url",
    'Magento_Customer/js/model/customer',
    'OnePica_Affirm/js/action/gift-wrapper-processing'
], function ($, $t, loadScript, fullScreenLoader, quote, url, customer, giftWrapper) {

    /**
     * Init address data
     *
     * @param checkout
     * @param type
     * @returns {*}
     */
    function initAddress(checkout, type) {
        var address, street;
        if (type == 'shipping') {
            address = quote.shippingAddress();
        } else if (type == 'billing') {
            address = quote.billingAddress();
        }

        checkout[type] = {};
        checkout[type].name = {
            "full": address.firstname + ' ' + address.lastname
        };
        if (address.street[0]) {
            street = address.street[0];
        }

        checkout[type].address = {
            "line1": street,
            "city": address.city,
            "state": address.regionCode,
            "zipcode": address.postcode,
            "country": address.countryId
        };

        if (address.street[1]) {
            checkout[type].address.line2 = address.street[1];
        }

        if (address.telephone) {
            checkout[type].phone_number = address.telephone;
        }
        if (!customer.isLoggedIn()) {
            checkout[type].email = quote.guestEmail;
        } else if (address.email) {
            checkout[type].email = address.email;
        }
        return checkout;
    }

    /**
     * Init items data
     *
     * @param checkout
     * @returns {*}
     */
    function initItems(checkout) {
        var items = quote.getItems();
        checkout.items = [];
        for (var i = 0; i < items.length; i++) {
            checkout.items[i] = {};
            checkout.items[i].display_name = items[i].name;
            checkout.items[i].sku = items[i].sku;
            checkout.items[i].unit_price = parseInt(items[i].price * 100);
            checkout.items[i].qty = items[i].qty;
            checkout.items[i].item_image_url = items[i].thumbnail;
            checkout.items[i].item_url = url.build(items[i].product.request_path);
        }
        return checkout;
    }

    /**
     * Init totals data
     *
     * @param checkout
     * @returns {*}
     */
    function initTotals(checkout) {
        var totals = quote.getTotals()();

        checkout.shipping_amount = totals.base_shipping_amount * 100;
        checkout.total = totals.grand_total * 100;
        checkout.tax_amount = totals.base_tax_amount * 100;
        return checkout;
    }

    function applyGiftWrapper(checkout) {
        var wrapItems = giftWrapper();
        checkout.items = checkout.items.concat(wrapItems);
        return checkout;
    }
    return function(response) {
        response = JSON.parse(response);
        var checkout = {};
        checkout = initAddress(checkout, 'shipping');
        checkout = initAddress(checkout, 'billing');
        checkout = initItems(checkout);
        checkout = applyGiftWrapper(checkout);
        checkout = initTotals(checkout);

        fullScreenLoader.startLoader();

        checkout.merchant = {} || checkout.merchant;
        checkout.metadata = {} || checkout.metadata;
        checkout.metadata = {
            "shipping_type": quote.shippingMethod().carrier_title
        };

        //init merchant from config provider
        checkout.merchant = window.checkoutConfig.payment['affirm_gateway'].merchant;

        checkout.order_id = response.order_increment_id;
        if (response.discounts) {
            checkout.discounts = response.discounts;
        }

        checkout.config = {} || checkout.config;
        checkout.config = window.checkoutConfig.payment['affirm_gateway'].config;
        affirm.checkout(checkout);
        affirm.checkout.post();
    }
});
