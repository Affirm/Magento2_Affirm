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
