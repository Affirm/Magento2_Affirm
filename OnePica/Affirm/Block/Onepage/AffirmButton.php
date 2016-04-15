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

namespace OnePica\Affirm\Block\Onepage;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;

/**
 * Class AffirmButton
 *
 * @package OnePica\Affirm\Block\Onepage
 */
class AffirmButton extends Template
{
    /**
     * Button template
     *
     * @var string
     */
    protected $_template = 'OnePica_Affirm::onepage/button.phtml';

    /**
     * Get button image from system configs
     *
     * @return bool|mixed
     */
    public function getButtonImageSrc()
    {
        $buttonSrc = $this->_scopeConfig->getValue('payment/affirm_gateway/checkout_button_code');
        if ($buttonSrc) {
            return $buttonSrc;
        }
        return false;
    }

    /**
     * Get checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }
}
