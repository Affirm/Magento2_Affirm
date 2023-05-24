<?php

namespace Astound\Affirm\Plugin;

/**/

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class SaveInvoiceAmountToCapture
{
    const LAST_INVOICE_AMOUNT = 'last_invoice_amount';

    public function beforeExecute(
        \Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation $subject,
        OrderPaymentInterface $payment,
        InvoiceInterface $invoice,
        string $operationMethod
    ) {
        $invoiceAmountToCapture = $payment->formatAmount($invoice->getGrandTotal(), true);
        $payment->setAdditionalInformation(self::LAST_INVOICE_AMOUNT, $invoiceAmountToCapture);
        return null;
    }
}
