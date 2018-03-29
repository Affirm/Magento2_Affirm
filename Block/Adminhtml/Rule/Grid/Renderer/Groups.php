<?php
namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;
class Groups extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $hlp = $om->get('Astound\Affirm\Helper\Data');
		$groups = $row->getData('cust_groups');
        if (!$groups) {
            return __('Restricts For All');
        }
        $groups = explode(',', $groups);
        
        $html = '';
        foreach($hlp->getAllGroups() as $row)
        {
            if (in_array($row['value'], $groups)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }
}