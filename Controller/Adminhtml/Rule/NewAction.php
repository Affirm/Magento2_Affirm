<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;
use Magento\Framework\App\ResponseInterface;

class NewAction extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->_forward('edit');
    }
}