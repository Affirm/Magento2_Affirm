<?php

namespace Astound\Affirm\Plugin;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class UpdateAuthMessage
{
    /**
     * @var Data
     */
    protected $priceHelper;

    /**
     * @param Data $priceHelper
     */
    public function __construct(
        Data $priceHelper
    ) {
        $this->priceHelper = $priceHelper;
    }

    /**
     * Updates order comment message with current store currency amount
     *
     * @param AuthorizeCommand $subject
     * @param $result
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return \Magento\Framework\Phrase
     */
    public function afterExecute(
        AuthorizeCommand $subject,
        $result,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {
        $order_currency_code = $order->getOrderCurrencyCode();
        $new_amount = $this->priceHelper->currency($amount, true, false);
        $_message = __($result->getText(), $order_currency_code . " " . $new_amount);
        return $_message;
    }
}
