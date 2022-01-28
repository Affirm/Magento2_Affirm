<?php
/**
 * Affirm
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm. All rights reserved.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Affirm\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;

/**
 * Update Financing Program for customer on login
 */
class UpdateFinancingProgramOnLogin implements ObserverInterface
{
    /**
     * Init
     *
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $sessionFinancingProgramValue = $this->_customerSession->getAffirmCustomerMfp();
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $observer->getCustomer();
            if (!empty($sessionFinancingProgramValue) &&
                ($customer->getAffirmCustomerMfp() != $sessionFinancingProgramValue)
            ) {
                $customerData = $customer->getDataModel();
                $customerData->setCustomAttribute('affirm_customer_mfp', $sessionFinancingProgramValue);
                $customer->updateData($customerData);
                $customer->save();
            }
        }
        return $this;
    }
}
