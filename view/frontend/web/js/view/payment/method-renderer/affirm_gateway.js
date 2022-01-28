/**
 * Affirm
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm. All rights reserved.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/action/set-payment-information',
        'Affirm/js/action/prepare-affirm-checkout',
        'Affirm/js/action/send-to-affirm-checkout',
        'Affirm/js/action/verify-affirm',
        'Affirm/js/action/inline-checkout'
    ],
    function ($, Component, quote, additionalValidators,
              urlBuilder, errorProcessor, Messages, setPaymentAction,
              initChekoutAction, sendToAffirmCheckout, verifyAffirmAction, inlineCheckout) {

        'use strict';

        return Component.extend({
            defaults: {
                template: 'Affirm/payment/form',
                transactionResult: ''
            },

            /**
             * Init Affirm specify message controller
             */
            initAffirm: function() {
                this.messageContainer = new Messages();
            },

            /**
             * Payment code
             *
             * @returns {string}
             */
            getCode: function () {
                return 'affirm_gateway';
            },

            /**
             * Get payment info
             *
             * @returns {info|*|indicators.info|z.info|Wd.$get.info|logLevel.info}
             */
            getInfo: function () {
                return window.checkoutConfig.payment['affirm_gateway'].info
            },

            /**
             * Get affirm logo src from config
             *
             * @returns {*}
             */
            getAffirmLogoSrc: function () {
                return window.checkoutConfig.payment['affirm_gateway'].logoSrc;
            },

            /**
             * Get visible
             *
             * @returns {*}
             */
            getVisibleType: function() {
                return window.checkoutConfig.payment['affirm_gateway'].visibleType;
            },

            /**
             * Show Affirm Checkout Education Modal
             *
             * @returns {*}
             */
            getEdu: function() {
                if (!window.checkoutConfig.payment['affirm_gateway'].edu) {
                    return "You will be redirected to Affirm to securely complete your purchase. Just fill out a few pieces of basic information and get a real-time decision. Checking your eligibility won\'t affect your credit score."
                }
            },

            /**
             * Show Affirm Checkout Education Modal
             *
             * @returns {*}
             */
            getEduHTML: function() {
                if (window.checkoutConfig.payment['affirm_gateway'].edu) {
                    let timestamp = 1
                    $('form').change(function(e){
                        if (timestamp == 1 || e.timeStamp > timestamp+500) {
                            timestamp = e.timeStamp
                            inlineCheckout.inlineCheckout()
                        }
                    })

                    $('.action-apply').click(function(){
                        inlineCheckout.updateInlineCheckout()
                    })

                    $('.action-cancel').click(function(){
                        inlineCheckout.updateInlineCheckout()
                    })

                    inlineCheckout.inlineCheckout()
                }
            },

            /**
             * Change place order button text
             *
             * @returns {*}
             */
            getAffirmTitle: function() {
                if (!window.checkoutConfig.payment['affirm_gateway'].edu) {
                    return "Continue with Affirm"
                } else {
                    return "Place Order"
                }
            },

            /**
             * Continue to Affirm redirect logic
             */
            continueInAffirm: function() {
                var self = this;
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    $.when(setPaymentAction(self.messageContainer, {'method': self.getCode()})).done(function() {
                        $.when(initChekoutAction(self.messageContainer)).done(function(response) {
                            sendToAffirmCheckout(response);
                        });
                    }).fail(function(){
                        self.isPlaceOrderActionAllowed(true);
                    });
                    return false;
                }
            },

            /**
             * Init payment
             */
            initialize: function () {
                var _self = this;
                this._super();
                $.when(verifyAffirmAction(_self.messageContainer)).done(function(response){
                    if (response) {
                       _self.selectPaymentMethod();
                    }
                }).fail(function(response){
                    errorProcessor.process(response, _self.messageContainer);
                });
            }
        });
    }
);
