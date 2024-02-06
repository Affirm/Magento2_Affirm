<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends \Astound\Affirm\Controller\Adminhtml\Rule
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
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');
        return $resultForward;
    }
}