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

namespace Astound\Affirm\Model;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Report\Order;
use Magento\Customer\Api\Data\CustomerInterface as CustomerDataObject;
use Astound\Affirm\Model\Config as AffirmConfig;

/**
 * Class Checkout for Affirm
 * This class is a wrapper for the Affirm checkout process
 *
 * @package Astound\Affirm\Model
 */
class Checkout
{
    /**
     * Customer ID
     *
     * @var int
     */
    protected $customerId;

    /**
     * Checkout session object
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Quote management object
     *
     * @var \Magento\Quote\Api\CartManagementInterfaces
     */
    protected $quoteManagement;

    /**
     * Current checkout quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = null;

    /**
     * Customer session object
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Checkout helper data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * Magento order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Order sender object
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;


    /**
     * Affirm payment config model
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $config;

    /**
     * Init config object
     *
     * @param CartManagementInterface         $cartManagement
     * @param Session                         $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data   $checkoutData
     * @param OrderSender                     $orderSender
     * @param Config                          $config
     * @param array                           $params
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        OrderSender $orderSender,
        AffirmConfig $config,
        $params = array()
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $cartManagement;
        $this->customerSession = $customerSession;
        $this->checkoutData = $checkoutData;
        $this->orderSender = $orderSender;
        $this->config = $config;
        if (isset($params['quote'])) {
            $this->quote = $params['quote'];
        }
        if (isset($params['config']) && $params['config'] instanceof AffirmConfig) {
            $this->config = $params['config'];
        }

        $this->_customerSession = isset($params['session'])
        && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;
    }

    /**
     * Return current quote from checkout session.
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote(){
        if(null == $this->quote){
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Place order based on prepared quote
     */
    public function place($token)
    {
        if (!$this->getQuote()->getGrandTotal()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Affirm can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
        }
        if (!$token) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Token is absent, some problem with response from Affirm happened.'
                )
            );
        }
        $this->initToken($token);
        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }
        $this->getQuote()->collectTotals();
        $this->ignoreAddressValidation();
        $this->order = $this->quoteManagement->submit($this->getQuote());

        switch ($this->order->getState()) {
            // even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
            case \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT:
                // TODO
                break;
            // regular placement, when everything is ok
            case \Magento\Sales\Model\Order::STATE_PROCESSING:
            case \Magento\Sales\Model\Order::STATE_COMPLETE:
            case \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW:
                $this->orderSender->send(($this->order));
                $this->checkoutSession->start();
                break;
            default:
                break;
        }
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get method checkout
     *
     * @return string
     */
    protected function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->getQuote()->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->getQuote())) {
                $this->getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function prepareGuestQuote()
    {
        $quote = $this->getQuote();
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     */
    protected function ignoreAddressValidation()
    {
        $this->getQuote()->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->getQuote()->getIsVirtual()) {
            $this->getQuote()->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->config->getValue('requireBillingAddress')
                && !$this->getQuote()->getBillingAddress()->getEmail()
            ) {
                $this->getQuote()->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * Setter for customer
     *
     * @param CustomerDataObject $customerData
     * @return $this
     */
    public function setCustomerData(CustomerDataObject $customerData)
    {
        $this->getQuote()->assignCustomer($customerData);
        $this->customerId = $customerData->getId();
        return $this;
    }

    /**
     * Retrieve order instance
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Init token
     * Save payment quote information to additional information
     *
     * @param string $token
     */
    protected function initToken($token)
    {
        if ($token) {
            $payment = $this->getQuote()->getPayment();
            $payment->setAdditionalInformation('checkout_token', $token);
            $payment->save();
        }
    }

    /**
     * Setter for customer with billing and shipping address changing ability
     *
     * @param CustomerDataObject $customerData
     * @param Address|null $billingAddress
     * @param Address|null $shippingAddress
     * @return $this
     */
    public function setCustomerWithAddressChange(
        CustomerDataObject $customerData,
        $billingAddress = null,
        $shippingAddress = null
    ) {
        $this->getQuote()->assignCustomerWithAddressChange($customerData, $billingAddress, $shippingAddress);
        $this->customerId = $customerData->getId();
        return $this;
    }
}
