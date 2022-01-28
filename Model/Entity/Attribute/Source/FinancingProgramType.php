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

namespace Affirm\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 *  Financing Program Type source model
 *
 * @package Affirm\Model\Entity\Attribute\Source
 */
class FinancingProgramType extends AbstractSource
{
    const FINANCING_PROGRAM_EXCLUSIVELY = 0;

    const FINANCING_PROGRAM_INCLUSIVELY = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function getAllOptions()
    {
        return [
            ['label' => __('Exclusive'), 'value' => self::FINANCING_PROGRAM_EXCLUSIVELY],
            ['label' => __('Inclusive'), 'value' => self::FINANCING_PROGRAM_INCLUSIVELY]
        ];
    }
}
