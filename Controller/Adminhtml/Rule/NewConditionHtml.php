<?php
namespace Affirm\Controller\Adminhtml\Rule;

class NewConditionHtml extends \Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->newConditions('conditions');
    }
}