<?php

namespace Astound\Affirm\Model\Rule\Condition;
class Address extends \Magento\Rule\Model\Condition\AbstractCondition
{
    protected $_directoryCountry;

    protected $_directoryAllregion;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_directoryCountry = $directoryCountry;
        $this->_directoryAllregion = $directoryAllregion;
    }

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = $this->getAttributeOption();
        unset($attributes['payment_method']);
        $attributes['base_subtotal'] = __('Subtotal');
        $attributes['total_qty'] = __('Total Items Quantity');
        $attributes['weight'] = __('Total Weight');
        $attributes['shipping_method'] = __('Shipping Method');
        $attributes['postcode'] = __('Shipping Postcode');
        $attributes['region'] = __('Shipping Region');
        $attributes['region_id'] = __('Shipping State/Province');
        $attributes['country_id'] = __('Shipping Country');
        $attributes['street'] = __('Address Line');
        $attributes['city'] = __('City');

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    public function getOperatorSelectOptions()
    {
        $operators = $this->getOperatorOption();
        if ($this->getAttribute() == 'street') {
            $operators = array(
                '{}' => __('contains'),
                '!{}' => __('does not contain'),
                '{%' => __('starts from'),
                '%}' => __('ends with'),
            );
        }

        $type = $this->getInputType();
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($operators as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value' => $k, 'label' => $v);
            }
        }
        return $opt;
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getDefaultOperatorInputByType()
    {
        $op = parent::getDefaultOperatorInputByType();
        $op['string'][] = '{%';
        $op['string'][] = '%}';
        return $op;
    }

    public function getDefaultOperatorOptions()
    {
        $op = parent::getDefaultOperatorOptions();
        $op['{%'] = __('starts from');
        $op['%}'] = __('ends with');

        return $op;
    }

    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;
        switch ($op) {
            case '{%':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue, 0, strlen($value)) == $value;
                }
                break;
            case '%}':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue, -strlen($value)) == $value;
                }
                break;
            default:
                return parent::validateAttribute($validatedValue);
                break;
        }
        return $result;
    }

    protected function _isArrayOperatorType()
    {
        $ret = false;
        if (method_exists($this, 'isArrayOperatorType')) {
            $ret = $this->isArrayOperatorType();
        } else {
            $op = $this->getOperator();
            $ret = ($op === '()' || $op === '!()');
        }

        return $ret;
    }
}