<?php
namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;
use \Astound\Affirm\Helper\Data;

class Methods extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
        $v = $row->getData('methods');
        if (!$v) {
            return __('Allows All');
        }
        $v = explode(',', $v);
        
        $html = '';
        foreach($this->affirmData->getAllMethods() as $row)
        {
            if (in_array($row['value'], $v)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }

}