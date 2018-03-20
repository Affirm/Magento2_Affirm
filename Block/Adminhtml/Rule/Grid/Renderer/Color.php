<?php

namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;

class Color extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getData($this->getColumn()->getIndex());
        if ($status == 1) {
            $color = "10a900";
            $value = "Active";
        } else {
            $color = "ff031b";
            $value = "Inactive";
        }

        return '<div style="text-align:center; color:#FFF;font-weight:bold;background:#' . $color . ';border-radius:8px;width:100%">' . $value . '</div>';
    }

}