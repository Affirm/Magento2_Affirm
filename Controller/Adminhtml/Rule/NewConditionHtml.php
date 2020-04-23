<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;

class NewConditionHtml extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->newConditions('conditions');
    }
}