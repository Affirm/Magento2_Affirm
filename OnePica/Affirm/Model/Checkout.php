<?php
namespace OnePica\Affirm\Model;

use \Magento\Quote\Api\CartManagementInterface;
use \Magento\Checkout\Model\Session;

/**
 * Class Checkout for Affirm
 * This class is a wrapper for the Affirm checkout process
 *
 * @package OnePica\Affirm\Model
 */
class Checkout
{
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
    protected $quote;

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
     * Inject objects to the checkout model
     *
     * @param CartManagementInterface         $cartManagement
     * @param Session                         $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data   $checkoutData
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $cartManagement;
        $this->quote = $this->checkoutSession->getQuote();
        $this->customerSession = $customerSession;
        $this->checkoutData = $checkoutData;
    }

    /**
     * Place order based on prepared quote
     */
    public function place()
    {
        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }
        $this->quote->collectTotals();
        $this->ignoreAddressValidation();
        $order = $this->quoteManagement->submit($this->quote);
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
        if (!$this->quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->quote)) {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function prepareGuestQuote()
    {
        $quote = $this->quote;
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
        $this->quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->quote->getIsVirtual()) {
            $this->quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->quote->getValue('requireBillingAddress')
                && !$this->quote->getBillingAddress()->getEmail()
            ) {
                $this->quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }
}
