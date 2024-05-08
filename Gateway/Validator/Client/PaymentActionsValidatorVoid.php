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
use Astound\Affirm\Helper\ErrorTracker;
use Astound\Affirm\Gateway\Helper\Util;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
/**
 * Class PaymentActionsValidatorVoid
 */
class PaymentActionsValidatorVoid extends PaymentActionsValidator
{
    /**#@+
     * Define constants
     */
    const RESPONSE_TYPE = 'type';
    const RESPONSE_TYPE_VOID = 'void';
    /**#@-*/

    
    /**
     * Product collection factory
     *
     * @var \Astound\Affirm\Helper\ErrorTracker
     */
    public $errorTracker;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorTracker $errorTracker,
        Util $util
    )
    {
        parent::__construct($resultFactory, $errorTracker, $util);
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

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined, please, try again later.')];
            $this->errorTracker->logErrorToAffirm(
                transaction_step: self::RESPONSE_TYPE_VOID,
                error_type: ErrorTracker::TRANSACTION_DECLINED,
                error_message: $errorMessages[0]->render()
            );
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Validate response type
     *
     * @param array $response
     * @return bool
     */
    public function validateResponseType(array $response)
    {
        return ($response[self::RESPONSE_TYPE] == self::RESPONSE_TYPE_VOID);
    }
}