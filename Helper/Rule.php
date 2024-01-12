<?php

namespace Astound\Affirm\Helper;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product;

/**
 * Rule helper
 *
 * @package Astound\Affirm\Helper
 */
class Rule extends Payment
{

    protected $_allRules = null;

    public function getRules()
    {
        if (is_null($this->_allRules))
        {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $hlp = $om->create('Astound\Affirm\Model\Rule');
            $this->_allRules = $hlp->getCollection()->addFieldToFilter('is_active', 1);
            $this->_allRules->load();
            foreach ($this->_allRules as $rule){
                $rule->afterLoad();
            }
        }

        return  $this->_allRules;
    }

    public function isAslowasAllowedPerRule($position)
    {
        if(isset($position)) {
            switch ($position) {
                case 'cc':
                    return $this->isQuoteItemsDisabledByPaymentRestRules();
                    break;
                default:
                    return true;
            }
        }
        return true;
    }

    public function isQuoteItemsDisabledByPaymentRestRules()
    {
        foreach ($this->getRules() as $rule){
            if ($rule->restrictByName(\Astound\Affirm\Model\Ui\ConfigProvider::CODE)){
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $checkoutsession = $om->get('Magento\Checkout\Model\Session');
                $quote = $checkoutsession->getQuote();
                $isValid = (bool) $rule->validate($quote);
                if ($isValid) {
                    return false;
                }
            }
        }
        return true;
    }
}
