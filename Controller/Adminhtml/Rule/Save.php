<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\Filter\FilterInput;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Astound\Affirm\Controller\Adminhtml\Rule
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
     * Save
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Astound\Affirm\Model\Rule');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new FilterInput(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                unset($data['rule']);

                $model->addData($data);
                $model->loadPost($data); // rules
                $this->_prepareForSave($model);

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                } else {
                    $resultRedirect->setPath('*/*/new');

                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $resultRedirect->setPath('*/*/');
    }

    public function _prepareForSave($model)
    {
        $fields = array('stores', 'cust_groups', 'methods');
        foreach ($fields as $f){
            $val = $model->getData($f);
            $model->setData($f, '');
            if (is_array($val)){
                $model->setData($f, ',' . implode(',', $val) . ',');
            }
        }

        return true;
    }
}