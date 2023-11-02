<?php
/**
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2021 Affirm, Inc.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Model\Plugin\Payment;

use Magento\Sales\Model\Order\Payment;
use Astound\Affirm\Model\Config;

/**
 * Class CanCapturePartial
 *
 * @package Astound\Affirm\Model\Plugin\Payment
 */
class CanCapturePartial
{
  /**
     * Affirm config model payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;


  /**
   * Define constants
   */
  const DEFAULT_COUNTRY_CODE = 'USA';
  const PARTIAL_CAPTURE_COUNTRIES = ['USA', 'CAN'];

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
    $countryCode = self::DEFAULT_COUNTRY_CODE;
    if (isset($subject->getData()['additional_information']['country_code'])) {
      $countryCode = $subject->getData()['additional_information']['country_code'];
    }

    if (!$this->affirmPaymentConfig->getPartialCapture() || !in_array( $countryCode, self::PARTIAL_CAPTURE_COUNTRIES ) ) {
      return false;
    }
    return $result;
  }
}
