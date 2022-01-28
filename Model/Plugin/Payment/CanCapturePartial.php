<?php
/**
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Affirm\Model\Plugin\Payment;

use Magento\Sales\Model\Order\Payment;
use Affirm\Model\Config;

/**
 * Class CanCapturePartial
 *
 * @package Affirm\Model\Plugin\Payment
 */
class CanCapturePartial
{
  /**
   * Constructor
   *
   * @param Config $configAffirm
   */
  public function __construct(
    Config $configAffirm
  )
  {
    $this->affirmPaymentConfig = $configAffirm;
  }

  /**
   * Plugin to verify if Partial Capture is enabled in config
   *
   * @param Magento\Sales\Model\Order\Payment $subject
   * @param callable                          $result
   * @return bool
   */
  public function afterCanCapturePartial(Payment $subject, $result)
  {
    if (!$this->affirmPaymentConfig->getPartialCapture()) {
      return false;
    }
    return $result;
  }
}
