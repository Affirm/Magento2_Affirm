<?php
namespace Astound\Affirm\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_counter;
    protected $_firstTime = true;

    protected $objectManager;

    protected $coreRegistry;

    public function __construct(Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry
    )
    {
        $this->objectManager = $objectManager;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    public function getAllGroups()
    {
        $customerGroups = $this->objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection')->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>__('NOT LOGGED IN')));
        }

        return $customerGroups;
    }
	
	public function getAllMethods()
    {
        $hash = [];
        foreach ($this->scopeConfig->getValue('payment') as $code=>$config){
            if (!empty($config['title'])){
                $label = '';
                if (!empty($config['group'])){
                    $label = ucfirst($config['group']) . ' - ';
                }
                $label .= $config['title'];
                $hash[$code] = $label;
                
            }
        }
        asort($hash);
        
        $methods = [];
        foreach ($hash as $code => $label){
            $methods[] = ['value' => $code, 'label' => $label];    
        }
        
        return $methods;      
    }

    public function getStatuses()
    {
        return array(
            '1' => __('Active'),
            '0' => __('Inactive'),
        );
    }
}