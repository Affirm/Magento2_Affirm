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
    "OnePica_Affirm/js/model/affirm"
], function ($, $t, loadScript, fullScreenLoader, quote, url, customer, affirmCheckout) {

    return function(response) {
        fullScreenLoader.startLoader();
        var result = JSON.parse(response), giftWrapItems = result.wrapped_items, checkoutObj;

        affirmCheckout.prepareOrderData(result);
        if (giftWrapItems !== 'undefined') {
            affirmCheckout.addItems(giftWrapItems);
        }
        affirm.ui.error.on("close", function(){
            $.mage.redirect(window.checkoutConfig.payment['affirm_gateway'].merchant.cancel_url);
        });
        checkoutObj = affirmCheckout.getData();
        affirm.checkout(checkoutObj);
        affirm.checkout.post();
    }
});
