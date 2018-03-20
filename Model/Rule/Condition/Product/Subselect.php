<?php
namespace Astound\Affirm\Model\Rule\Condition\Product;

class Subselect extends \Magento\SalesRule\Model\Rule\Condition\Product\Subselect
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Astound\Affirm\Model\Rule\Condition\Product\Subselect')
            ->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'qty'                       => __('total quantity'),
            'base_row_total'            => __('total amount excl. tax'),
            'base_row_total_incl_tax'   => __('total amount incl. tax'),
            'row_weight'                => __('total weight'),
        ));
        return $this;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this->validateNotModel($object);
    }

    public function validateNotModel($object)
    {
        $attr = $this->getAttribute();
        $total = 0;
        if ($object->getAllItems()) {
            $validIds = array();
            foreach ($object->getAllItems() as $item) {


                if ($item->getProduct()->getTypeId() == 'configurable') {
                    $item->getProduct()->setTypeId('skip');
                }

                //can't use parent here
                if (\Magento\SalesRule\Model\Rule\Condition\Product\Combine::validate(
                    $item
                )
                ) {
                    $itemParentId = $item->getParentItemId();
                    if (is_null($itemParentId)) {
                        $validIds[] = $item->getItemId();
                    } else {
                        if (in_array($itemParentId, $validIds)) {
                            continue;
                        } else {
                            $validIds[] = $itemParentId;
                        }
                    }


                    $total += $item->getData($attr);
                }

                if ($item->getProduct()->getTypeId() === 'skip') {
                    $item->getProduct()->setTypeId('configurable');
                }
            }
        }

        return $this->validateAttribute($total);
    }
}