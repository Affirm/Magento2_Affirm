<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\Controller\ResultFactory;

class Duplicate extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    public $resultFactory;

    /**
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Please select a rule to duplicate.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        try {
            $model = $this->_objectManager->create('Astound\Affirm\Model\Rule')->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }

            $rule = clone $model;
            $rule->setIsActive(0);
            $rule->setId(null);
            $rule->save();

            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            $this->messageManager->addSuccessMessage(__('The rule has been duplicated. Please feel free to activate it.'));
            $resultRedirect->setPath('*/*/edit', array('id' => $rule->getId()));
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
    }
}