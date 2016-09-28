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

namespace Astound\Affirm\Model\Plugin\Checkout\CustomerData;

class Cart
{
    /**
     * Affirm payment model instance
     *
     * @var \Astound\Affirm\Helper\Payment
     */
    protected $affirmPaymentHelper;

    /**
     * @param \Astound\Affirm\Helper\Payment $helperAffirm
     */
    public function __construct(\Astound\Affirm\Helper\Payment $helperAffirm)
    {
        $this->affirmPaymentHelper = $helperAffirm;
    }

    /**
     * Check if Affirm method available for current quote items combination
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['allow_affirm_quote_aslowas'] = $this->affirmPaymentHelper->isAffirmAvailable();
        return $result;
    }
}
