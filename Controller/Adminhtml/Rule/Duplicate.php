<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
class Duplicate extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        if (!$id) {
            $this->messageManager->addError(__('Please select a rule to duplicate.'));
            return $this->_redirect('*/*');
        }
        try {
            $model = $this->_objectManager->create('Astound\Affirm\Model\Rule')->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }

            $rule = clone $model;
            $rule->setIsActive(0);
            $rule->setId(null);
            $rule->save();

            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            $this->messageManager->addSuccess(__('The rule has been duplicated. Please feel free to activate it.'));

            return $this->_redirect('*/*/edit', array('id' => $rule->getId()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('*/*');
            return;
        }
    }
}