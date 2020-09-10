<?php
/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Directory\Model\RegionFactory;

class AddressFormat implements ObserverInterface
{
    protected $regionFactory;
    public function __construct(
        RegionFactory $regionFactory
    )
    {
        $this->regionFactory = $regionFactory;
    }
    /**
     * Save region if address object has region_id but not region name
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $address = $observer->getEvent()->getAddress();
        if($address->getAddressType()) {
            if ($address->getRegion() == null) {
                $regionId = $address->getRegionId();
                /** @var \Magento\Directory\Model\Region $region */
                $region = $this->regionFactory->create();
                $region->getResource()->load($region, $regionId);
                $address->setRegion($region->getName());
                $address->setRegionCode($region->getCode());
                $address->save();
            }
        }
    }
}
