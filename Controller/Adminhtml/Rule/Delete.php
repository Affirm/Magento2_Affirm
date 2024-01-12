<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;


class Delete extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    /**
     * Delete
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Astound\Affirm\Model\Rule');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('*/*/');
    }
}