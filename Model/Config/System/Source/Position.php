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

/**
 * Class Position
 *
 * @package Astound\Affirm\Model\Config
 */
class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            '0' => __('↑ Center top'),
            '1' => __('↓ Center bottom'),
            '2' => __('↖ Sidebar top'),
            '3' => __('↙ Sidebar bottom')
        ];
    }

    /**
     * Get checkout cart position
     *
     * @return array
     */
    public function getCCPosition()
    {
        return [
            '0' => __('↑ Center Top'),
            '1' => __('↓ Center bottom'),
            '2' => __('↑ Near checkout button'),
        ];
    }

    /**
     * Bml positions source getter for Catalog Product Page
     *
     * @return array
     */
    public function getBmlPositionsCPP()
    {
        return [
            '0' => __('↑ Header (center) top'),
            '1' => __('↓ Header (center) bottom'),
            '2' => __('↑ Near checkout button')
        ];
    }

    /**
     * Block placement for Product Detail Page
     *
     * @return array
     */
    public function getBlockPlacementPDP()
    {
        return [
            '0' => __('After Price'),
            '1' => __('Before Price'),
            '2' => __('End of Product Info')
        ];
    }
}
