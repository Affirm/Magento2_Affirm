/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
], function ($,storage, urlBuilder) {
    'use strict';
    var configData = window.checkoutConfig.payment['affirm_gateway'];
    var checkoutObject
    var initAffirmInline = true
    return {
        inlineCheckout: function(){
            let serviceUrl = urlBuilder.createUrl('/affirm/checkout/inline', {}), result;
            storage.get(
                serviceUrl
            ).done(
                function(response) {
                    if(!(checkoutObject == response)) {
                        affirm.ui.ready(function() {
                            affirm.checkout(JSON.parse(response))
                            affirm.checkout.inline({
                                merchant: {
                                    inline_container: "affirm-inline-checkout"
                                },
                            });
                        })
                        initAffirmInline = false
                    } else {
                        affirm.checkout.inline({
                            container: "affirm-inline-checkout",
                            data: JSON.parse(response),
                        });
                    }
                    checkoutObject = response
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        },

        updateInlineCheckout : function(){
            var _self = this;
            let serviceUrl = urlBuilder.createUrl('/affirm/checkout/inline', {}), result;
            storage.get(
                serviceUrl
            ).done(
                function(response) {
                    setTimeout(function(){
                        $('.action-apply').click(function(){
                            _self.updateInlineCheckout()
                        })
                        $('.action-cancel').click(function(){
                            _self.updateInlineCheckout()
                        })
                        $('.action-update').click(function(){
                            _self.updateInlineCheckout()
                        })
                    },
                    3000);
                    affirm.checkout.inline({
                        container: "affirm-inline-checkout",
                        data: JSON.parse(response),
                    });
                }
            ).fail(
                function (response) {
                    console.log(response)
                }
            )
        }
    }
})
