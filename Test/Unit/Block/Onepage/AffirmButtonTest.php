<?php
namespace Astound\Affirm\Test\Unit\Block\Onepage;

use Magento\Framework\View\Element\Template\Context;
use Astound\Affirm\Block\Onepage\AffirmButton;
use \Magento\Checkout\Model\Session;
use \Astound\Affirm\Helper\Payment;

class AffirmButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    /**
     * @var
     */
    protected $scopeConfigMock;

    /**
     * @var
     */
    protected $contextMock;

    /**
     * @var
     */
    protected $sessionMock;

    /**
     * @var
     */
    protected $helperMock;

    /**
     * @var
     */
    protected $block;

    /**
     * Init main test class
     */
    public function setUp()
    {
        $this->contextMock = $this
            ->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->block = new AffirmButton(
            $this->contextMock,
            $this->helperMock,
            $this->sessionMock
        );

        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test method isButtonEnabled
     * @dataProvider getConfigProvider
     */
    public function testIsButtonEnabled($arg, $result)
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with($arg)
            ->willReturn($result);
        $realResult = $this->block->isButtonEnabled();
        $this->assertEquals($result, $realResult);
    }

    /**
     * @return array
     */
    public function getConfigProvider()
    {
        return [
            ['payment/affirm_gateway/enable_checkout_button', true]
        ];
    }

    /**
     * @test
     */
    public function testGetCheckoutUrl()
    {
        $path = 'checkout';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $context = $this->_objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            ['urlBuilder' => $urlBuilder]
        );

        $button = $this->_objectManagerHelper->getObject('Astound\Affirm\Block\Onepage\AffirmButton', ['context' => $context]);
        $this->assertEquals($url . $path, $button->getCheckoutUrl());
    }

    /**
     * @test
     */
    public function testGetButtonImageSrc()
    {
        $value = 'payment/affirm_gateway/checkout_button_code';
        $testButtonSrc = 'http://d33v4339jhl8k0.cloudfront.net/docs/assets/54ab19f8e4b08393789ca141/images/54ebbdc6e4b086c0c0968fa2/file-cmGEKUeWtK.png';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with($value)
            ->willReturn($testButtonSrc);
        $realResult = $this->block->getButtonImageSrc();
        $this->assertEquals($testButtonSrc, $realResult);
    }
}
