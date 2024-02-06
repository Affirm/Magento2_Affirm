<?php

namespace Astound\Affirm\Plugin;

use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyInterface;

class UpdatePrependMessage
{
    /**
     * Constants
     */
    const LAST_INVOICE_AMOUNT = 'last_invoice_amount';
    const AFFIRM_PAYMENT_TITLE = 'Affirm';

    /**
     * @var CurrencyInterface
     */
    public $currencyInterface;

    /**
     * @param CurrencyInterface $currencyInterface
     */
    public function __construct(
        CurrencyInterface $currencyInterface
    ) {
        $this->currencyInterface = $currencyInterface;
    }

    /**
     * Updates order comment message with separately stored invoice amount
     *
     * @param Payment $subject
     * @param string|History $messagePrependTo
     * @return array|void
     */
    public function beforePrependMessage(Payment $subject, $messagePrependTo)
    {
        $payment_method = $subject->getMethodInstance()->getTitle();
        if ($payment_method != self::AFFIRM_PAYMENT_TITLE) {
            return null;
        }
        $order_currency_code = $subject->getOrder()->getOrderCurrencyCode();
        $invoice_amount = $subject->getAdditionalInformation(self::LAST_INVOICE_AMOUNT);
        if (isset($invoice_amount)) {
            $new_amount = $this->currencyInterface->format($invoice_amount, false, 2);
            $messagePrependTo = __($messagePrependTo->getText(), $order_currency_code . " " . $new_amount);
            return [$messagePrependTo];
        } else {
            return null;
        }

    }
}
