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

use Astound\Affirm\Model\Config\System\Source\Size;

/**
 * Class SizeTest
 *
 * @package Astound\Affirm\Test\Unit\Model\Config\System\Source
 */
class SizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $sizeSourceModel;

    /**
     * Test
     */
    public function setUp()
    {
        $this->sizeSourceModel = new Size();
    }

    /**
     * @test
     *
     * Test to option array for size source model
     */
    public function testToOptionArray()
    {
        self::assertEquals(
            [
                array('value' => '120x90', 'label'  => '120x90'),
                array('value' => '150x100', 'label' => '150x100'),
                array('value' => '170x100', 'label' => '170x100'),
                array('value' => '190x100', 'label' => '190x100'),
                array('value' => '234x60', 'label'  => '234x60'),
                array('value' => '300x50', 'label'  => '300x50'),
                array('value' => '468x60', 'label'  => '468x60'),
                array('value' => '300x250', 'label' => '300x250'),
                array('value' => '336x280', 'label' => '336x280'),
                array('value' => '540x200', 'label' => '540x200'),
                array('value' => '728x90', 'label'  => '728x90'),
                array('value' => '800x66', 'label'  => '800x66'),
                array('value' => '250x250', 'label' => '250x250'),
                array('value' => '280x280', 'label' => '280x280'),
                array('value' => '120x240', 'label' => '120x240'),
                array('value' => '120x600', 'label' => '120x600'),
                array('value' => '234x400', 'label' => '234x400'),
            ],
            $this->sizeSourceModel->toOptionArray()
        );
    }
}
