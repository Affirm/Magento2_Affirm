<?php
namespace Astound\Affirm\Test\Model;

use Astound\Affirm\Model\Config;

/**
 * Class ConfigTest
 *
 * @package Astound\Affirm\Test\Model
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Astound\Affirm\Model\Config
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $storeMock;

    protected function setUp()
    {
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeMock = $this->getMock('Magento\Store\Api\Data\StoreInterface');
        $this->model = new Config(
            $this->scopeConfig,
            $this->storeManager
        );
    }

    /**
     * Test Bml display config
     *
     * @param $section
     * @param $expectedValue
     * @param $expected
     * @dataProvider getBmlProvider
     */
    public function testGetBmlDisplay($section, $expectedValue, $expected)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                ['affirm/' . Config::METHOD_BML . '_' . $section . '/' . 'display', 'website', null, $expectedValue]
            ]));
        $this->assertEquals($expected, $this->model->getBmlDisplay($section));
    }

    /**
     * Test get bml position method
     *
     * @param $section
     * @param $expectedValue
     * @param $expected
     * @dataProvider positionProvider
     */
    public function testGetBmlPosition($section, $expectedValue, $expected)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                ['affirm/' . Config::METHOD_BML . '_' . $section . '/' . 'position', 'website', null, $expectedValue]
            ]));
        $this->assertEquals($expected, $this->model->getBmlPosition($section));
    }

    /**
     * @return array
     */
    public function positionProvider()
    {
        return [
            ['product', 0, 0]
        ];
    }

    /**
     * Bml display provider
     *
     * @return array
     */
    public function getBmlProvider()
    {
        return [
            ['category', true, true],
            ['product', false, false],
            ['category', false, false],
            ['product', true, true]
        ];
    }
}
