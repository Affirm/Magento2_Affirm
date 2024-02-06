<?php
namespace Astound\Affirm\Block\Adminhtml\Rule\Grid\Renderer;
use Magento\Store\Model\System\Store;

class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Product collection factory
     *
     * @var \Magento\Store\Model\System\Store
     */
    public $affirmData;

    public function __construct(
        \Magento\Store\Model\System\Store $store,
    )
    {
        $this->store = $store;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $stores = $row->getData('stores');
        if (!$stores) {
            return __('Restricts in All');
        }
        
        $html = '';
        $data = $this->store->getStoresStructure(false, explode(',', $stores));
        foreach ($data as $website) {
            $html .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $html .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $html .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $html;
    }
}