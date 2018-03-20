<?php
namespace Astound\Affirm\Controller\Adminhtml\Rule;

class NewActionHtml extends \Astound\Affirm\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->newConditions('actions');
    }
}