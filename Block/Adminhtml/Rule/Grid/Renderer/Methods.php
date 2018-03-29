<?php
namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;

class Methods extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $hlp = $om->get('Astound\Affirm\Helper\Data');
        
        $v = $row->getData('methods');
        if (!$v) {
            return __('Allows All');
        }
        $v = explode(',', $v);
        
        $html = '';
        foreach($hlp->getAllMethods() as $row)
        {
            if (in_array($row['value'], $v)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }

}