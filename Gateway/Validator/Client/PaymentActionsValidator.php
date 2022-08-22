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

namespace Astound\Affirm\Gateway\Validator\Client;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Astound\Affirm\Gateway\Helper\Util;

/**
 * Class PaymentActionsValidator
 */
class PaymentActionsValidator extends AbstractResponseValidator
{
    /**
     * Validate response
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = '';

        if ( (isset($response['checkout_status']) && $response['checkout_status'] == 'confirmed')
            || (isset($response['status']) && $response['status'] == 'authorized')
            || (isset($response['type']) && $response['type'] == 'capture'))
        {
            // Pre-Auth/Auth/Capture uses amount_ordered from payment
            $_payment = $validationSubject['payment']->getPayment();
            $payment_data = $_payment->getData();
            $amount = $payment_data['amount_ordered'];
        } elseif ( (isset($response['type']) && $response['type'] == 'refund') ) {
            // Refund (including partial) uses grand_total from creditmemo (credit memo invoice)
            $_payment = $validationSubject['payment']->getPayment();
            $_creditMemo = $_payment->getData()['creditmemo'];
            $amount = $_creditMemo->getGrandTotal();
        } else {
            // Partial capture (US only) uses validationSubject
            $amount = SubjectReader::readAmount($validationSubject);
        }

        $amountInCents = Util::formatToCents($amount);

        $errorMessages = [];
        $validationResult = $this->validateResponseCode($response)
            && $this->validateTotalAmount($response, $amountInCents);

        if (!$validationResult) {
            $errorMessages = (isset($response[self::ERROR_MESSAGE])) ?
                [__($response[self::ERROR_MESSAGE]) . __(' Affirm status code: ') . $response[self::RESPONSE_CODE]]:
                [__('Transaction has been declined, please, try again later.')];
            throw new \Magento\Framework\Validator\Exception(__($errorMessages[0]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
