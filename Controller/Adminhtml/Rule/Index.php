<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;

class Index extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Astound_Affirm::rule');
        $resultPage->getConfig()->getTitle()->prepend(__('Payment Restrictions Rules'));
        $resultPage->addBreadcrumb(__('Payment Restrictions Rules'), __('Payment Restrictions Rules'));
        return $resultPage;
    }
}