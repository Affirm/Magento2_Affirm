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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Months
 *
 * @package Affirm\Model\Config\System\Source
 */
class Months implements ArrayInterface
{
    /**
     * Get month list
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            3  => "3",
            6  => "6",
            12 => "12"
        ];
    }
}
