<?php
/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace OnePica\Affirm\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

/**
 * Class Block Error
 *
 * @package OnePica\Affirm\Block\Adminhtml
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
