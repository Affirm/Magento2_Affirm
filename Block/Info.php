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

namespace Astound\Affirm\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Payment Block Info class
 *
 * @package Astound\Affirm\Block
 */
class Info extends ConfigurableInfo
{
    /**
     * Get affirm main url
     */
    const AFFIRM_MAIN_URL = 'https://www.affirm.com/u';

    /**
     * Changed standard template
     *
     * @var string
     */
    protected $_template = 'Astound_Affirm::payment/info/edit.phtml';

    /**
     * Retrieve translated label
     *
     * @param string $field
     * @return Phrase|string
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Retrieve affirm main url
     *
     * @return string
     */
    public function getAffirmMainUrl()
    {
        return self::AFFIRM_MAIN_URL;
    }
}
