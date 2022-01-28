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

namespace Affirm\Model\Config\System\Source;

/**
 * Source model for sizes in admin panel
 *
 * @package Affirm\Model\Config\System\Source
 */
class Size implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Default sizes
     *
     * @var array
     */
    public static $sizes = [
        '"Pay over time banner sizes"',
        '--120x90',
        '--150x100',
        '--170x100',
        '--190x100',
        '--234x60',
        '--300x50',
        '"Make Monthly Payments banner sizes"',
        '--468x60',
        '--300x250',
        '--336x280',
        '--540x200',
        '--728x90',
        '--800x66',
        '--250x250',
        '--280x280',
        '--120x240',
        '--120x600',
        '--234x400'
    ];

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = array();
        foreach (self::$sizes as $size) {
            $options[] = array('value' => $size, 'label' => $size);
        }
        return $options;
    }
}
