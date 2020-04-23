<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;

class Edit extends \Astound\Affirm\Controller\Adminhtml\Rule
{
   public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Astound\Affirm\Model\Rule');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        } else {
            $this->_prepareForEdit($model);
        }
        $this->_coreRegistry->register('affirm_payment_restriction_rule', $model);
        $this->_initAction();
        if($model->getId()) {
            $title = __('Edit Payment Restrictions Rule `%1`', $model->getName());
        } else {
            $title = __("Add new Payment Restriction Rule");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }

    public function _prepareForEdit(\Astound\Affirm\Model\Rule $model)
    {
        $fields = array('stores', 'cust_groups', 'methods');
        foreach ($fields as $f){
            $val = $model->getData($f);
            if (!is_array($val)){
                $model->setData($f, explode(',', $val));
            }
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        return true;
    }
}