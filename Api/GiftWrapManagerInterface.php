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

namespace Affirm\Api;

/**
 * Interface GiftWrapManagerInterface
 *
 * @package Affirm\Api
 */
interface GiftWrapManagerInterface
{
    /**
     * Retrieve all gift wrap items
     *
     * @return mixed
     */
    public function getWrapItems();

    /**
     * Retrieve printed card item
     *
     * @return mixed
     */
    public function getPrintedCardItem();
}
