<?php
/**
 * Affirm
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm. All rights reserved.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Affirm\Gateway\Validator\Client;

use Magento\Payment\Gateway\Helper\SubjectReader;

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
        }

        return $this->createResult($validationResult, $errorMessages);
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
