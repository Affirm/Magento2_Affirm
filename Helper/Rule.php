<?php

namespace Astound\Affirm\Helper;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product;
use \Astound\Affirm\Model\Rule as ModelRule;
use Magento\Checkout\Model\Session;

/**
 * Rule helper
 *
 * @package Astound\Affirm\Helper
 */
class Rule extends Payment
{
    public $_allRules = null;

    /**
     * Product collection factory
     *
     * @var \Astound\Affirm\Model\Rule
     */
    protected $modelRule;

    /**
     * Product collection factory
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $sessions;

    public function __construct(
        \Astound\Affirm\Model\Rule $modelRule,
        \Magento\Checkout\Model\Session $sessions
    )
    {
        $this->modelRule = $modelRule;
        $this->sessions = $sessions;
    }

    public function getRules()
    {
        if ($this->_allRules === null)
        {
            $this->_allRules = $this->modelRule->getCollection()->addFieldToFilter('is_active', 1);
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
                $quote = $this->sessions->getQuote();
                $isValid = (bool) $rule->validate($quote);
                if ($isValid) {
                    return false;
                }
            }
        }
        return true;
    }
}
