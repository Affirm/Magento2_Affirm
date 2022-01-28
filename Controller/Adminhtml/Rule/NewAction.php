<?php
namespace Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\App\ResponseInterface;

class NewAction extends \Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->_forward('edit');
    }
}