<?php

namespace Astound\Affirm\Plugin;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Model\ResourceModel\Rule\Collection;

class RestrictPaymentMethods
{
    public $_allRules = null;

     /**
     * CheckoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutsession;
    
    /**
     * Affirm
     *
     * @var \Astound\Affirm\Model\ResourceModel\Rule\Collection
     */
    public $collection;

    /**
     * Initialize affirm checkout
     *
     * @param Session                                   $checkoutSession
     * @param Rule                                      $rule
     */

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutsession,
        Collection $collection
    )
    {
        $this->checkoutsession = $checkoutsession;
        $this->collection = $collection;
    }

    public function afterGetActiveList(\Magento\Payment\Model\PaymentMethodList $subject, $result)
    {
        $methods = $result;


        $quote = $this->checkoutsession->getQuote();

        $address = $quote->getShippingAddress();
        foreach ($methods as $k => $method) {
            foreach ($this->getRules($address) as $rule) {
                if ($rule->restrict($method)) {
                    if ($rule->validate($address)) {
                        unset($methods[$k]);
                    }
                }
            }
        }

        return $methods;

    }

    public function getRules($address)
    {
        if ($this->_allRules === null) {
            $this->_allRules = $this->collection->addAddressFilter($address)->load();
            foreach ($this->_allRules as $rule) {
                $rule->afterLoad();
            }
        }

        return $this->_allRules;
    }
}