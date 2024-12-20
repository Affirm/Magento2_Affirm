<?php
declare(strict_types=1);

namespace Astound\Affirm\Plugin;

use Astound\Affirm\Model\Checkout;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;


/**
 * Set additionalInformation on payment for Hosted Pro method
 */
class SetCheckoutTokenGraphQL
{
    public Checkout $checkout;

    /**
     * @param \Astound\Affirm\Model\Checkout $checkout
     */
    public function __construct(
        Checkout $checkout
    ) {
        $this->checkout = $checkout;
    }

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
        $payment = $cart->getPayment();
        $paymentMethod = $payment->getMethod();
        if ($paymentMethod !== ConfigProvider::CODE || !array_key_exists('checkout_token', $paymentData['affirm'])) {
            return;
        }

        /**
         * @see \Astound\Affirm\Model\Checkout::initToken()
         */
        $quoteCurrencyCode = $cart->getCurrency()->getQuoteCurrencyCode();
        $countryCode = $this->checkout->getCountryCodeByCurrency($quoteCurrencyCode);
        if (!isset($countryCode[1])) {
            throw new LocalizedException(__(
                'Affirm not supported with currency %1.',
                $quoteCurrencyCode
            ));
        }

        $token = $paymentData['affirm']['checkout_token'];
        $payment->setAdditionalInformation('checkout_token', $token);
        $payment->setAdditionalInformation('country_code', $countryCode[1]);

        $payment->save();
    }
}

