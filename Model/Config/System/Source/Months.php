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

namespace Astound\Affirm\Model\Config\System\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Months
 *
 * @package Astound\Affirm\Model\Config\System\Source
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
