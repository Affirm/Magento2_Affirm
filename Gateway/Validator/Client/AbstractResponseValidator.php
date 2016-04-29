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

use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class AbstractResponseValidator
 */
abstract class AbstractResponseValidator extends AbstractValidator
{
    /**#@+
     * Define constants
     */
    const RESPONSE_CODE = 'status_code';
    const AMOUNT = 'amount';
    const TOTAL = 'total';
    const ERROR_MESSAGE = 'message';
    /**#@-*/

    /**
     * Validate response code
     *
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return !(isset($response[self::RESPONSE_CODE]));
    }

    /**
     * Validate total amount
     *
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        return isset($response[self::AMOUNT])
            && ($response[self::AMOUNT]) === $amount;
    }
}
