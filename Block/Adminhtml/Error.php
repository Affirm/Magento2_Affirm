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

namespace Affirm\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

/**
 * Class Block Error
 *
 * @package Affirm\Block\Adminhtml
 */
class Error extends Template
{
    /**
     * Fixed url for affirm's virtual terminal.
     */
    const VIRTUAL_TERMINAL_URL = "http://help.merchants.affirm.com/article/67-virtual-terminal-overview";

    /**
     * Retrieve affirm's virtual url
     *
     * @return string
     */
    public function getVirtualUrl()
    {
        return self::VIRTUAL_TERMINAL_URL;
    }
}
