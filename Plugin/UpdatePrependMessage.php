<?php

namespace Astound\Affirm\Plugin;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Sales\Model\Order\Payment;

class UpdatePrependMessage
{
    const LAST_INVOICE_AMOUNT = 'last_invoice_amount';

    protected $priceHelper;

    public function __construct(Data $priceHelper) {
        $this->priceHelper = $priceHelper;
    }

    public function beforePrependMessage(Payment $subject, $messagePrependTo)
    {
        $order_currency_code = $subject->getOrder()->getOrderCurrencyCode();
        $invoice_amount = $subject->getAdditionalInformation(self::LAST_INVOICE_AMOUNT);
        if (isset($invoice_amount)) {
            $new_amount = $this->priceHelper->currency($invoice_amount, true, false);
            $messagePrependTo = __($messagePrependTo->getText(), $order_currency_code . " " . $new_amount);
            return [$messagePrependTo];
        } else {
            return null;
        }

    }
}
