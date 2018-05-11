<?php
namespace Astound\Affirm\Model\ResourceModel\Rule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Astound\Affirm\Model\Rule', 'Astound\Affirm\Model\ResourceModel\Rule');
    }
	
	public function addAddressFilter($address)
    {
        $this->addFieldToFilter('is_active', 1);
        
        $storeId = $address->getQuote()->getStoreId();
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');
        
        $groupId = 0;
        if ($address->getQuote()->getCustomerId()){
            $groupId = $address->getQuote()->getCustomer()->getGroupId();    
        }
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        
        return $this;
    }    
}