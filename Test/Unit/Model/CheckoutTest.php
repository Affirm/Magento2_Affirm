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

namespace Astound\Affirm\Test\Model;

/**
 * Class CheckoutTest
 *
 * @package Astound\Affirm\Test\Model
 */
class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Astound\Affirm\Model\Checkout | \Astound\Affirm\Model\Checkout
     */
    protected $checkoutAffirm;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \'Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Customer
     */
    protected $customerMock;

    /**
     * Test checkout model class
     *
     * @test
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote',
            [
                'getId', 'assignCustomer', 'assignCustomerWithAddressChange', 'getBillingAddress',
                'getShippingAddress', 'isVirtual', 'addCustomerAddress', 'collectTotals', '__wakeup',
                'save', 'getCustomerData'
            ], [], '', false);
        $this->customerAccountManagementMock = $this->getMock(
            '\Magento\Customer\Model\AccountManagement',
            [],
            [],
            '',
            false
        );
        $this->objectCopyServiceMock = $this->getMockBuilder('\Magento\Framework\DataObject\Copy')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutModel = $this->objectManager->getObject(
            'Astound\Affirm\Model\Checkout',
            [
                'params' => [
                    'quote' => $this->quoteMock,
                    'session' => $this->customerSessionMock,
                ],
                'accountManagement' => $this->customerAccountManagementMock,
                'objectCopyService' => $this->objectCopyServiceMock
            ]
        );
        parent::setUp();
    }

    /**
     * @test
     */
    public function testSetCustomerData()
    {
        $customerDataMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('assignCustomer')->with($customerDataMock);
        $customerDataMock->expects($this->once())
            ->method('getId');
        $this->checkoutModel->setCustomerData($customerDataMock);
    }

    /**
     * @test
     */
    public function testSetCustomerWithAddressChange()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customerDataMock */
        $customerDataMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        /** @var \Magento\Quote\Model\Quote\Address $customerDataMock */
        $quoteAddressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->quoteMock
            ->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($customerDataMock, $quoteAddressMock, $quoteAddressMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->checkoutModel->setCustomerWithAddressChange($customerDataMock, $quoteAddressMock, $quoteAddressMock);
    }
}
