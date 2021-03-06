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
use Astound\Affirm\Logger\Logger;

/**
 * Class PaymentActionsValidator
 */
class PaymentActionsValidator extends AbstractResponseValidator
{
    /**
     * Constructor
     *
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);
        $amountInCents = Util::formatToCents($amount);

        $errorMessages = [];
        $validationResult = $this->validateResponseCode($response)
            && $this->validateTotalAmount($response, $amountInCents);

        if (!$validationResult) {
            $errorMessages = (isset($response[self::ERROR_MESSAGE])) ?
                [__('Affirm error code:') . $response[self::RESPONSE_CODE] . __(' error: ') .
                    __($response[self::ERROR_MESSAGE])]:
                [__('Transaction has been declined, please, try again later.')];
            $this->logger->debug('Astound\Affirm\Gateway\Validator\Client\PaymentActionsValidator::validate', $errorMessages);
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
