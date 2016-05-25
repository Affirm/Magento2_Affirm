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

namespace Astound\Affirm\Block\Promotion\CartPage;

use Astound\Affirm\Block\Promotion\AsLowAsAbstract;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;

/**
 * Class AsLowAs
 *
 * @package Astound\Affirm\Block\Promotion\CartPage
 */
class AsLowAs extends AsLowAsAbstract
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Cart page block.
     *
     * @param Template\Context             $context
     * @param ConfigProvider               $configProvider
     * @param \Astound\Affirm\Model\Config $configAffirm
     * @param Session                      $session
     * @param array                        $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        \Astound\Affirm\Model\Config $configAffirm,
        Session $session,
        array $data = []
    ) {
        $this->checkoutSession = $session;
        parent::__construct($context, $configProvider, $configAffirm, $data);
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Validate block before showing on front in checkout cart
     * There can be added new validators by needs.
     *
     * @return boolean
     */
    public function validate()
    {
        if ($this->getQuote()) {
            $total = $this->getQuote()->getBaseSubtotal();
            $isAvailableFlag = $this->getPaymentConfigValue('active');
            $maxLimit = $this->getPaymentConfigValue('max_order_total');
            $minLimit = $this->getPaymentConfigValue('min_order_total');
            if ($isAvailableFlag && $minLimit && $maxLimit && $total < $maxLimit && $total > $minLimit) {
                return true;
            }
        }
        return false;
    }
}
