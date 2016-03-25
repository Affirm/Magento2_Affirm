/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'affirmCheckout',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (Component, AffirmCheckout, quote, additionalValidator) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnePica_Affirm/payment/form',
                transactionResult: ''
            },

            getCode: function () {
                return 'affirm_gateway';
            },

            /**
             * @override
             */
            placeOrder: function () {
                if (additionalValidator.validate()) {
                    //TODO: we should send request from there
                }
            }
        });
    }
);
