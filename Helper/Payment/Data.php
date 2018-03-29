<?php

namespace Astound\Affirm\Helper\Payment;
class Data extends \Magento\Payment\Helper\Data
{
    protected $_allRules = null;

    public function getStoreMethods($store = null, $quote = null)
    {
        $methods = parent::getStoreMethods($store, $quote);
        if (!$quote) {
            return $methods;
        }

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