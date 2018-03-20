<?php

namespace Astound\Affirm\Plugin;

class RestrictPaymentMethods
{
    protected $objectManager;
    protected $_allRules = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    public function afterGetActiveList(\Magento\Payment\Model\PaymentMethodList $subject, $result)
    {
        $methods = $result;

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutsession = $om->get('Magento\Checkout\Model\Session');
        $quote = $checkoutsession->getQuote();

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
        if (is_null($this->_allRules)) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $hlp = $om->create('Astound\Affirm\Model\Rule');
            $this->_allRules = $hlp->getCollection()->addAddressFilter($address)->load();
            foreach ($this->_allRules as $rule) {
                $rule->afterLoad();
            }
        }

        return $this->_allRules;
    }
}