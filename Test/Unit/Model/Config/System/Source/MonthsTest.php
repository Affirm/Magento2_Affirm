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

namespace Astound\Affirm\Test\Unit\Model\Config\System\Source;

use Astound\Affirm\Model\Config\System\Source\Months;

/**
 * Class MonthsTest
 *
 * @package Astound\Affirm\Test\Unit\Model\Config\System\Source
 */
class MonthsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Astound\Affirm\Model\Config\System\Source\Months sourceModel
     */
    protected $sourceModel;

    /**
     * Tests
     */
    public function setUp()
    {
        $this->sourceModel = new Months();
    }

    /**
     * Test months source model
     *
     * @test
     */
    public function testToOptionArray()
    {
        $result = $this->sourceModel->toOptionArray();
        $expected = [
            3  => "3",
            6  => "6",
            12 => "12"
        ];
        $this->assertEquals($expected, $result);
    }
}
