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
use Astound\Affirm\Helper\ErrorTracker;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class PaymentActionsValidator
 */
class PaymentActionsValidator extends AbstractResponseValidator
{
    /**
     * Validate response
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = '';
        $_payment = $validationSubject['payment']->getPayment();
        $transaction_step = '';

        if ( (isset($response['checkout_status']) && $response['checkout_status'] == 'confirmed')
            || (isset($response['status']) && $response['status'] == 'authorized')
        ) {
            // Pre-Auth/Auth uses amount_ordered from payment
            $payment_data = $_payment->getData();
            $amount = $payment_data['amount_ordered'];

            $transaction_step = (isset($response['checkout_status']) && $response['checkout_status'] == 'confirmed') ?
                'pre_auth' : 'auth';
        } elseif ( (isset($response['type']) && $response['type'] == 'capture')
            || (isset($response['type']) && $response['type'] == 'split_capture')
        ) {
            // Capture or partial capture (US only) uses stored value from invoice total
            $amount = $_payment->getAdditionalInformation(self::LAST_INVOICE_AMOUNT);
            $transaction_step = 'capture';
        } elseif ( (isset($response['type']) && $response['type'] == 'refund')
        ) {
            // Refund (including partial) uses grand_total from creditmemo (credit memo invoice)
            $_creditMemo = $_payment->getData()['creditmemo'];
            $amount = $_creditMemo->getGrandTotal();
            $transaction_step = 'refund';
        } else {
            $amount = SubjectReader::readAmount($validationSubject);
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $util = $om->create('Astound\Affirm\Gateway\Helper\Util');
        $amountInCents = $util->formatToCents($amount);

        $errorMessages = [];
        $validationResult = $this->validateResponseCode($response)
            && $this->validateTotalAmount($response, $amountInCents);

        if (!$validationResult) {
            $errorMessages = (isset($response[self::ERROR_MESSAGE])) ?
                [__($response[self::ERROR_MESSAGE]) . __(' Affirm status code: ') . $response[self::RESPONSE_CODE]]:
                [__('Transaction has been declined, please, try again later.')];
            
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var $errorTracker \Astound\Affirm\Helper\ErrorTracker */
            $errorTracker = $om->create('Astound\Affirm\Helper\ErrorTracker');
            $errorTracker->logErrorToAffirm(
                transaction_step: $transaction_step,
                error_type: ErrorTracker::TRANSACTION_DECLINED,
                error_message: $errorMessages[0]->render()
            );
            
            throw new \Magento\Framework\Validator\Exception(__($errorMessages[0]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}