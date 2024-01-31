<?php
namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;
use \Astound\Affirm\Helper\Data;
class Groups extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * Product collection factory
     *
     * @var \Astound\Affirm\Helper\Data
     */
    protected $affirmData;

    public function __construct(
        \Astound\Affirm\Helper\Data $affirmData,
    )
    {
        $this->affirmData = $affirmData;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
		$groups = $row->getData('cust_groups');
        if (!$groups) {
            return __('Restricts For All');
        }
        $groups = explode(',', $groups);
        
        $html = '';
        foreach($this->affirmData->getAllGroups() as $row)
        {
            if (in_array($row['value'], $groups)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }
}