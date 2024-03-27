<?php
/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Gateway\Request;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Astound\Affirm\Gateway\Helper\Util;
use Magento\Framework\Math\Random;

/**
 * Class CaptureRequest
 */
class CaptureRequest extends AbstractDataBuilder
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $transactionId = $payment->getAdditionalInformation(self::TRANSACTION_ID) ?:
            $payment->getAdditionalInformation(self::CHARGE_ID);
        $countryCode = $payment->getAdditionalInformation(self::COUNTRY_CODE) ?: self::DEFAULT_COUNTRY_CODE;
        $order = $payment->getOrder();
        $storeId = isset($order) ? $order->getStoreId() : $this->_storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = null;
        }
        if ($this->affirmPaymentConfig->getPartialCapture($countryCode)) {
            $invoiceAmountToCapture = $payment->getAdditionalInformation(self::LAST_INVOICE_AMOUNT) ?:
                $buildSubject['amount'];
            $random = new Random();
            $util = new Util($random);
            $_amount = $util->formatToCents($invoiceAmountToCapture);
            $_body = [
                'amount' => $_amount
            ];
        } else {
            $_body = [];
        }
        return [
            'path' => "{$transactionId}/capture",
            'storeId' => $storeId,
            'body' => $_body,
            'country_code' => $countryCode,
        ];
    }
}
