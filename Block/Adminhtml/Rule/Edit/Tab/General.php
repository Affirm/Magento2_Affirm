<?php

namespace Astound\Affirm\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends Generic implements TabInterface
{
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data
    )
    {
        $this->_systemStore = $systemStore;
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

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('affirm_payment_restriction_rule');
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $hlp = $om->get('Astound\Affirm\Helper\Data');

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
            'options' => $hlp->getStatuses(),
        ]);

        $fieldset->addField('methods', 'multiselect', [
            'label' => __('Disable Selected Payment Methods'),
            'name' => 'methods[]',
            'values' => $hlp->getAllMethods(),
            'required' => true,
        ]);

        $fieldset->addField('cust_groups', 'multiselect', [
            'name' => 'cust_groups[]',
            'label' => __('Customer Groups'),
            'values' => $hlp->getAllGroups(),
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