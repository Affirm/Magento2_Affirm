<?php
namespace OnePica\Affirm\Test\Unit\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Astound\Affirm\Block\Info;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Context mock
     *
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * Config mock
     *
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var InfoInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentInfoModel;

    /**
     * Init Mock buidler
     */
    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMock(ConfigInterface::class);
        $this->paymentInfoModel = $this->getMock(InfoInterface::class);
    }

    /**
     * @test
     */
    public function testGetLabelMethod()
    {
        $info = new Info(
            $this->context,
            $this->config
        );
        $reflectionClass = new \ReflectionClass(get_class($info));
        $reflectionMethod = $reflectionClass
            ->getMethod('getLabel');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($info, array('Test label'));
        $this->assertEquals('Test label', $result);
    }
}
