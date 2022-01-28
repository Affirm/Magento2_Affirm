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

namespace Affirm\Block\Onepage;

use Magento\Framework\View\Element\Template;
use Affirm\Helper\Payment;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AffirmButton
 *
 * @package Affirm\Block\Onepage
 */
class AffirmButton extends Template
{
    /**
     * Affirm payment model instance
     *
     * @var \Affirm\Helper\Payment
     */
    protected $helper;

    /**
     * Current checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Current checkout quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Button template
     *
     * @var string
     */
    protected $_template = 'Affirm::onepage/button.phtml';

    /**
     * Affirm checkout button block
     *
     * @param Template\Context                $context
     * @param Payment                         $helper
     * @param \Magento\Checkout\Model\Session $session
     * @param array                           $data
     */
    public function __construct(
        Template\Context $context,
        \Affirm\Helper\Payment $helper,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->quote = $session->getQuote();
        parent::__construct($context, $data);
    }

    /**
     * Get button image from system configs
     *
     * @return bool|mixed
     */
    public function getButtonImageSrc()
    {
        $buttonSrc = $this->_scopeConfig->getValue(
            'payment/affirm_gateway/checkout_button_code',
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($buttonSrc) {
            return $buttonSrc;
        }
        return false;
    }

    /**
     * Get button image width from system configs
     *
     * @return bool|mixed
     */
    public function getButtonImageWidth()
    {
        $buttonWidth = $this->_scopeConfig->getValue(
            'payment/affirm_gateway/checkout_button_width',
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($buttonWidth) {
            return "width : ".$buttonWidth.'px';
        }
        return;
    }

    /**
     * Show button only if quote isn't virtual at all
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isAvailable()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * Get button availability
     *
     * @return bool|mixed
     */
    public function isAvailable()
    {
        return $this->helper->isAffirmAvailable() && $this->isButtonEnabled() ? true: false;
    }

    /**
     * Check is button enabled
     *
     * @return mixed
     */
    public function isButtonEnabled()
    {
        return $this->_scopeConfig->getValue(
            'payment/affirm_gateway/enable_checkout_button',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
