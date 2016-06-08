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

use Magento\Framework\Phrase;
use Astound\Affirm\Model\Config\System\Source\Position;

/**
 * Class PositionTest
 *
 * @package Astound\Affirm\Test\Unit\Model\Config\System\Source
 */
class PositionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Position source model
     *
     * @var \Astound\Affirm\Model\Config\System\Source\Position
     */
    protected $positionSourceModel;

    /**
     * Set up position source model instance
     */
    public function setUp()
    {
        $this->positionSourceModel = new Position();
    }
    /**
     * Test get catalog product page banner position
     */
    public function testGetBmlPositionsCPP()
    {
        self::assertEquals(
            [
                '0' => new Phrase('↑ Header (center) top'),
                '1' => new Phrase('↓ Header (center) bottom'),
                '2' => new Phrase('↑ Near checkout button')
            ],
            $this->positionSourceModel->getBmlPositionsCPP()
        );
    }

    /**
     * Test checkout cart banner position options.
     */
    public function testGetCCPosition()
    {
        self::assertEquals(
            [
                '0' => new Phrase('↑ Header (center) top'),
                '1' => new Phrase('↓ Header (center) bottom'),
                '2' => new Phrase('↑ Near checkout button')
            ],
            $this->positionSourceModel->getBmlPositionsCPP()
        );
    }

    /**
     * Test main to option array method
     */
    public function testToOptionArray()
    {
        self::assertEquals(
            [
                '0' => new Phrase('↑ Center top'),
                '1' => new Phrase('↓ Center bottom'),
                '2' => new Phrase('↖ Sidebar top'),
                '3' => new Phrase('↙ Sidebar bottom')
            ],
            $this->positionSourceModel->toOptionArray()
        );
    }
}
