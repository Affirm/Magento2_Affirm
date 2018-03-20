<?php
namespace Astound\Affirm\Model\Rule\Condition;
use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    protected $objectManager;

    protected $_eventManager = null;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->setType('Astound\Affirm\Model\Rule\Condition\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $addressCondition = $this->objectManager->create('Astound\Affirm\Model\Rule\Condition\Address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();

        $attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $attributes[] = array('value'=>'Astound\Affirm\Model\Rule\Condition\Address|'.$code, 'label'=>$label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Found', 'label'=>__('Product attribute combination')),
            array('value' => 'Astound\Affirm\Model\Rule\Condition\Product\Subselect', 'label'=>__('Products subselection')),
            array('label' => __('Conditions combination'), 'value' => $this->getType()),
            array('label' => __('Cart Attribute'),         'value' => $attributes),
        ));

        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
	
	public function validateNotModel($entity)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            if ($entity instanceof \Magento\Framework\Model\AbstractModel) {
                $validated = $cond->validate($entity);
            } elseif ($entity instanceof \Magento\Framework\DataObject
                && method_exists($cond, 'validateNotModel')
            ) {
                $validated = $cond->validateNotModel($entity);
            } elseif ($entity instanceof \Magento\Framework\DataObject) {
                $attribute = $entity->getData($cond->getAttribute());
                $validated = $cond->validateAttribute($attribute);
            } else {
                $validated = $cond->validateByEntityId($entity);
            }
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

}
