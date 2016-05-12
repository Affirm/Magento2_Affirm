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

namespace Astound\Affirm\Block\Promotion;

use Magento\Framework\View\Element\Template;

/**
 * Class Aslowas
 *
 * @package Astound\Affirm\Block\Promotion
 */
class Aslowas extends \Magento\Framework\View\Element\Template
{
    /**
     * As low as data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Inject block init data
     *
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Get widget data
     *
     * @return string
     */
    public function getWidgetData()
    {
        return json_encode($this->data);
    }
}
