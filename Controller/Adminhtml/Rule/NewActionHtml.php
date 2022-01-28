<?php
namespace Affirm\Controller\Adminhtml\Rule;

class NewActionHtml extends \Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->newConditions('actions');
    }
}