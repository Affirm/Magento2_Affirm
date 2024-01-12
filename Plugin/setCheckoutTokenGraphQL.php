<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Astound\Affirm\Plugin;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;


/**
 * Set additionalInformation on payment for Hosted Pro method
 */
class setCheckoutTokenGraphQL
{
    /**
     * Set Affirm Checkout Token
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param mixed $result
     * @param Quote $cart
     * @param array $paymentData
     * @return void
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        $result,
        Quote $cart,
        array $paymentData
    ): void
    {
        $paymentMethod = $cart->getPayment()->getMethod();
        if ($paymentMethod === 'affirm_gateway') {
            if(array_key_exists( 'checkout_token' , $paymentData['affirm'] )) {
                $payment = $cart->getPayment();
                $token =  $paymentData['affirm']['checkout_token'];
                $payment->setAdditionalInformation('checkout_token', $token);
                $payment->save();
            }
        }
    }
}

