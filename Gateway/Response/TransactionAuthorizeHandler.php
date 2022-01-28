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

namespace Affirm\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class TransactionAuthorizeHandler
 */
class TransactionAuthorizeHandler implements HandlerInterface
{
    /**#@+
     * Define constants
     */
    const TRANSACTION_OBJECT_ID = 'id';
    const TRANSACTION_ID = 'transaction_id';
    /**#@-*/

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setAdditionalInformation(self::TRANSACTION_ID, $response[self::TRANSACTION_OBJECT_ID]);
        $orderPayment->setTransactionId($response[self::TRANSACTION_OBJECT_ID]);
        $orderPayment->setIsTransactionClosed(false);
    }
}
