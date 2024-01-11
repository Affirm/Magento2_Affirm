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
use Astound\Affirm\Logger\Logger as affirmLogger;

/**
 * Class PaymentActionsValidatorVoid
 */
class PaymentActionsValidatorCaptureFailVoid extends PaymentActionsValidator
{
    /**
     * Affirm logging instance
     *
     * @var AffirmLogger
     */
    protected $affirmLogger;

    /**#@+
     * Define constants
     */
    const RESPONSE_TYPE = 'type';
    const RESPONSE_TYPE_VOID = 'void';
    /**#@-*/

    public function __construct(
        AffirmLogger $affirmLogger
    ) {
        $this->affirmLogger = $affirmLogger;
    }
    
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $errorMessages = [];
        $validationResult = $this->validateResponseCode($response)
            && $this->validateResponseType($response);
        
        $this->affirmLogger->debug('Astound\Affirm\Gateway\Validator\Client\PaymentActionsValidator:validate', array('LAST_INVOICE_AMOUNT not set'));
        
        if (!$validationResult) {
            $this->affirmLogger->debug('Astound\Affirm\Gateway\Validator\Client\PaymentActionsValidator:validate', array('Affirm API Failed'));
        }
        
        $errorMessages = [__('Transaction has been declined, please, try again later.')];

        $this->errorTracker(
            transaction_step: self::RESPONSE_TYPE_VOID,
            error_type: ErrorTracker::TRANSACTION_DECLINED,
            error_message: $errorMessages[0]->render()
        );

        throw new \Magento\Framework\Validator\Exception(__($errorMessages[0]));

    }

    /**
     * Validate response type
     *
     * @param array $response
     * @return bool
     */
    protected function validateResponseType(array $response)
    {
        return ($response[self::RESPONSE_TYPE] == self::RESPONSE_TYPE_VOID);
    }
}