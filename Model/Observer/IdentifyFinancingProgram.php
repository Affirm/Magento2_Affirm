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
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

/**
 * Identify Financing Program for customer
 */
class IdentifyFinancingProgram implements ObserverInterface
{
    /**
     * Init
     *
     */
    public function __construct(
        Session $customerSession,
        RequestInterface $request
    ) {
        $this->_customerSession = $customerSession;
        $this->request = $request;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {

        $financingProgramValue = $this->request->getParam('affirm_fpid');
        if (empty($financingProgramValue)) {
            return;
        }
        if ($this->_customerSession->isLoggedIn()) {
            $this->_updateLoggedInCustomerMFP($financingProgramValue);
        } else {
            $this->_updateGuestCustomerMFP($financingProgramValue);
        }
        return $this;
    }

    /**
     * Update for logged-in customer
     *
     * @param string $financingProgramValue
     */
    protected function _updateLoggedInCustomerMFP($financingProgramValue)
    {
        $customer = $this->_customerSession->getCustomer();
        $customerMFPValue = $customer->getAffirmCustomerMfp();
        if (empty($customerMFPValue) || ($financingProgramValue != $customerMFPValue)) {
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('affirm_customer_mfp', $financingProgramValue);
            $customer->updateData($customerData);
            $customer->save();
            //in case if customer logout to keep actual value during session
            $this->_updateGuestCustomerMFP($financingProgramValue);
        }
    }

    /**
     * Update for guest
     *
     * @param string $financingProgramValue
     */
    protected function _updateGuestCustomerMFP($financingProgramValue)
    {
        $sessionMFPValue = $this->_customerSession->getAffirmCustomerMfp();
        if (empty($sessionMFPValue) || ($financingProgramValue != $sessionMFPValue)) {
            $this->_customerSession->setAffirmCustomerMfp($financingProgramValue);
        }
    }
}
