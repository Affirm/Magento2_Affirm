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

namespace Astound\Affirm\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Astound\Affirm\Gateway\Helper\Util;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class CaptureStrategyCommand
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**#@+
     * Define constants
     */
    const AUTHORIZE = 'authorize';
    const ORDER_CAPTURE = 'order_capture';
    const CHECKOUT_TOKEN = 'checkout_token';
    const TRANSACTION_ID = 'transaction_id';
    const CHARGE_ID = 'charge_id';
    const LAST_INVOICE_AMOUNT = 'last_invoice_amount';
    const VOID = 'caputure_fail_void';
    /**#@-*/

    /**
     * Payment code
     *
     * @var string
     */
    protected $methodCode = 'affirm_gateway';

    /**
     * Command pool
     *
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * Scope configuration object
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Command\CommandPoolInterface $commandPool
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->commandPool = $commandPool;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);
           /** @var Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        
        $transactionId = $paymentInfo->getAdditionalInformation(self::TRANSACTION_ID) ?:
            $paymentInfo->getAdditionalInformation(self::CHARGE_ID);
        if (($paymentInfo instanceof Order\Payment) && !$transactionId) {
            $checkoutToken = $paymentInfo->getAdditionalInformation(self::CHECKOUT_TOKEN);
            if ($checkoutToken) {
                $this->commandPool
                    ->get(self::AUTHORIZE)
                    ->execute($commandSubject);
            }
        }

        if ($this->getConfigData('payment_action') == 'authorize_capture') {
            $last_invoice_amount = $paymentInfo->getAdditionalInformation(self::LAST_INVOICE_AMOUNT);
            $amountInCents = Util::formatToCents($last_invoice_amount);
            if ($amountInCents == 0) {
                return $this->commandPool
                    ->get(self::VOID)
                    ->execute($commandSubject);
            }
        }


        return $this->commandPool
            ->get(self::ORDER_CAPTURE)
            ->execute($commandSubject);
    }

    /**
     * Get config data
     *
     * @param        $field
     * @param null   $id
     * @param string $scope
     * @return mixed
     */
    public function getConfigData($field, $id = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->methodCode) {
            $path = 'payment/' . $this->methodCode . '/' . $field;
            $res = $this->scopeConfig->getValue($path, $scope, $id);
            return $res;
        }
        return false;
    }
}
