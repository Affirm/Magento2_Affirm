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
use Astound\Affirm\Model\Ui\ConfigProvider;
use Astound\Affirm\Model\Config;
use Astound\Affirm\Helper\Payment;

/**
 * Class Aslowas
 *
 * @package Astound\Affirm\Block\Promotion
 */
abstract class AslowasAbstract extends \Magento\Framework\View\Element\Template
{
    /**
     * Data which should be converted to json from the Block data.
     *
     * @var array
     */
    protected $data = ['apr', 'months', 'logo', 'script', 'public_api_key'];

    /**
     * Affirm config model payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Affirm config provider
     *
     * @var \Astound\Affirm\Model\Ui\ConfigProvider
     */
    protected $configProvider;

    /**
     * Affirm payment model instance
     *
     * @var Payment
     */
    protected $affirmPaymentHelper;

    /**
     * Block type
     *
     * @var string
     */
    protected $type;

    /**
     * Position of "As Low As" block
     *
     * @var string
     */
    protected $position;

    /**
     * Inject block init data
     *
     * @param Template\Context $context
     * @param ConfigProvider   $configProvider
     * @param Config           $configAffirm
     * @param Payment          $helperAffirm
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        Config $configAffirm,
        Payment $helperAffirm,
        array $data = []
    ) {
        if (isset($data['position']) && $data['position']) {
            $this->position = $data['position'];
        }

        $currentWebsiteId = $context->getStoreManager()->getStore()->getWebsiteId();
        $this->affirmPaymentConfig = $configAffirm;
        $this->affirmPaymentConfig->setWebsiteId($currentWebsiteId);
        $this->configProvider = $configProvider;
        $this->affirmPaymentHelper = $helperAffirm;

        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
        parent::__construct($context, $data);
    }

    /**
     * Get all needed data for As Low As
     * before render this block.
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->process();
        return parent::_beforeToHtml();
    }

    /**
     * Verify if "As Low As" allowed in the position
     *
     * @param $position
     * @return int|mixed
     */
    protected function isAllowed($position)
    {
        return $this->affirmPaymentConfig->isAslowasEnabled($position) && $this->affirmPaymentConfig->isCurrencyValid();
    }

    /**
     * Get specified data about As Low AS
     * from context of the block and convert it
     * to json format.
     *
     * @return string
     */
    public function getWidgetData()
    {
        if ($this->data && $this->affirmPaymentConfig->getAsLowAsLogo() &&
            $this->affirmPaymentConfig->getAsLowAsMonths()) {
            return $this->convertToJson($this->data);
        }
        return '';
    }

    /**
     * Specify all data for As Low AS widget.
     * Assign the data to the context of the block for simple
     * converting it to json format.
     */
    public function process()
    {
        if ($this->affirmPaymentConfig->getAsLowAsLogo() && $this->affirmPaymentConfig->getAsLowAsMonths()) {
            $this->setData('apr', $this->affirmPaymentConfig->getAsLowAsApr());
            $this->setData('months', $this->affirmPaymentConfig->getAsLowAsMonths());
            $this->setData('logo', $this->affirmPaymentConfig->getAsLowAsLogo());

            $configProvider = $this->configProvider->getConfig();
            if ($configProvider['payment'][ConfigProvider::CODE]) {
                $config = $configProvider['payment'][ConfigProvider::CODE];
                $this->setData('script', $config['script']);
                $this->setData('public_api_key', $config['apiKeyPublic']);
            }
            // Set max and min options amounts from payment configuration
            $this->setData('min_order_total', $this->getPaymentConfigValue('min_order_total'));
            $this->setData('max_order_total', $this->getPaymentConfigValue('max_order_total'));
        }
    }

    /**
     * Get is defined value from configuration
     *
     * @param string $value
     * @return bool|mixed
     */
    public function getPaymentConfigValue($value)
    {
        return $this->affirmPaymentConfig->getConfigData($value) ?
            $this->affirmPaymentConfig->getConfigData($value): false;
    }

    /**
     * Apply specific validation before show the block on front.
     * If validation will not passed, don't show this block in general.
     *
     * @return string|void
     */
    protected function _toHtml()
    {
        $isAllowed = $this->isAllowed($this->position);
        if ($this->validate() && $isAllowed) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Validate block before showing on front
     * Every child block class should have own validation rules
     * which will be applied before showing the block.
     *
     * @return boolean
     */
    abstract public function validate();
}
