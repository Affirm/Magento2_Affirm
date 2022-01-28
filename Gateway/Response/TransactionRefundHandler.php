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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class TransactionRefundHandler
 */
class TransactionRefundHandler implements HandlerInterface
{
    /**#@+
     * Define constants
     */
    const TRANSACTION_ID = 'transaction_id';
    /**#@-*/

    /**
     * Local date
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Constructor
     *
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $id = $this->localeDate->date()
            ->format('His');
        $id = ($id) ? $id : 1;
        $transactionId = $orderPayment->getAdditionalInformation(self::TRANSACTION_ID);
        $type = Transaction::TYPE_REFUND;
        $orderPayment->setTransactionId("{$transactionId}-{$id}-{$type}");
        $orderPayment->setIsTransactionClosed(true);
    }
}
