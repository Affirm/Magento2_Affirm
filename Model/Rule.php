<?php
namespace Astound\Affirm\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    protected $objectManager;
    protected $storeManager;


	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {

        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context, $registry, $formFactory, $localeDate, null, null, $data
        );

    }
	public function validate(\Magento\Framework\DataObject $object)
    {
        return $this->getConditions()->validateNotModel($object);
    }

    protected function _construct()
    {
        $this->_init('Astound\Affirm\Model\ResourceModel\Rule');
        parent::_construct();
    }
	
	public function restrict($method)
    {
        return (false !== strpos($this->getMethods(), ',' . $method->getCode() . ','));
    }

    public function restrictByName($methodname)
    {
        return (false !== strpos($this->getMethods(), ',' . $methodname . ','));
    }

    public function getConditionsInstance()
    {
        return $this->objectManager->create('Astound\Affirm\Model\Rule\Condition\Combine');
    }
	
	public function getActionsInstance()
    {
        return $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
    }

    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }

    public function afterSave()
    {
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        }

        return parent::afterSave();
    }

    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        $pattern = '~s:46:"Magento\\\SalesRule\\\Model\\\Rule\\\Condition\\\Product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches))
		{
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }

        return $result;
    }
}