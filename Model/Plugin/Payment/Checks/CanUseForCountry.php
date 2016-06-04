<?php
namespace Astound\Affirm\Model\Plugin\Payment\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Class CanUseForCountry
 *
 * @package Astound\Affirm\Model\Plugin\Payment\Checks
 */
class CanUseForCountry
{
    /**
     * Verify if afirm payment applicable
     *
     * @param \Magento\Payment\Model\Checks\CanUseForCountry $subject
     * @param callable                                       $method
     * @param MethodInterface                                $payment
     * @param Quote                                          $quote
     * @return bool
     */
    public function aroundIsApplicable(
        \Magento\Payment\Model\Checks\CanUseForCountry $subject,
        \Closure $method,
        MethodInterface $payment,
        Quote $quote
    ) {
        if ($payment->getCode() == \Astound\Affirm\Model\Ui\ConfigProvider::CODE) {
            if (!$quote->getCustomerId() && $quote->getIsVirtual()) {
                return false;
            }
        }
        return $method($payment, $quote);
    }
}
