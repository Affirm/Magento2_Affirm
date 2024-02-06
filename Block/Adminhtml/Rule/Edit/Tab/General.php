<?php

namespace Astound\Affirm\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use \Astound\Affirm\Helper\Data;

class General extends Generic implements TabInterface
{
    public $_systemStore;

    /**
     * Product collection factory
     *
     * @var \Astound\Affirm\Helper\Data
     */
    public $affirmData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Astound\Affirm\Helper\Data $affirmData,
        array $data
    )
    {
        $this->_systemStore = $systemStore;
        $this->affirmData = $affirmData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('General');
    }

    public function getTabTitle()
    {
        return __('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('affirm_payment_restriction_rule');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('general', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField('is_active', 'select', [
            'label' => __('Status'),
            'name' => 'is_active',
            'options' => $this->affirmData->getStatuses(),
        ]);

        $fieldset->addField('methods', 'multiselect', [
            'label' => __('Disable Selected Payment Methods'),
            'name' => 'methods[]',
            'values' => $this->affirmData->getAllMethods(),
            'required' => true,
        ]);

        $fieldset->addField('cust_groups', 'multiselect', [
            'name' => 'cust_groups[]',
            'label' => __('Customer Groups'),
            'values' => $this->affirmData->getAllGroups(),
            'note' => __('Leave empty or select all to apply the rule to any group'),
        ]);

        $fieldset->addField('stores', 'multiselect', [
            'label' => __('Stores'),
            'name' => 'stores[]',
            'values' => $this->_systemStore->getStoreValuesForForm(),
            'note' => __('Leave empty or select all to apply the rule to any'),
        ]);

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}