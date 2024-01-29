<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\Controller\ResultFactory;


class MassAction extends \Astound\Affirm\Controller\Adminhtml\Rule
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

    /**
     * MassAction
     *
     * @return void
     */
    public function execute()
    {

        $ids = $this->getRequest()->getParam('rules');
        $action = $this->getRequest()->getParam('action');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($ids && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $status = -1;
                switch($action) {
                    case 'delete':
                        $collection = $this->_objectManager->create('Astound\Affirm\Model\ResourceModel\Rule\Collection');

                        $collection->addFieldToFilter('rule_id', array('in'=>$ids));
                        $collection->walk($action);
                        $status = -1;
                        $message = __('You deleted the rule(s).');
                        break;
                    case 'activate':
                        $status = 1;
                        $message = __('You activated the rule(s).');
                        break;
                    case 'inactivate':
                        $status = 0;
                        $message = __('You deactivated the rule(s).');
                        break;
                }

                if($status > -1) {
                    $this->_objectManager->create('Astound\Affirm\Model\Rule')->massChangeStatus($ids, $status);
                }

                $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete/activate/deactivate rule(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule(s) to delete/activate/deactivate.'));
        $resultRedirect->setPath('*/*/');
    }
}