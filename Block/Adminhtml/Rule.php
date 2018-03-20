<?php
namespace Astound\Affirm\Block\Adminhtml;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_headerText = __('Payment Restriction Rules');
        $this->_addButtonLabel = __('Add Rule');
        parent::_construct();
    }
}