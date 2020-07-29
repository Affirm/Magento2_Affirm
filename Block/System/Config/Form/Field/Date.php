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

namespace Astound\Affirm\Block\System\Config\Form\Field;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

/**
 * Class Date
 */
class Date extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Core registry
     *
     * @var Registry $_coreRegistry
     */
    protected $_coreRegistry;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        if (!$this->_coreRegistry->registry('datepicker_loaded')) {
            $this->_coreRegistry->registry('datepicker_loaded', 1);
        }
        $html .= '<script type="text/javascript">
            require(["jquery"], function () {
                jQuery(document).ready(function () {
                    jQuery("#' . $element->getHtmlId() . '").datepicker( { dateFormat: "mm/dd/yy" } );
                    var el = document.getElementById("' . $element->getHtmlId() . '");
                    el.className = el.className + " datepicker";
                });
            });
            </script>';
        return $html;
    }
}
