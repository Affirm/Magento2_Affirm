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

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Astound\Affirm\Model\Ui\ConfigProvider;

/**
 * Class Aslowas
 *
 * @package Astound\Affirm\Block\Promotion
 */
abstract class AsLowAsAbstract extends \Magento\Framework\View\Element\Template
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
     * @param Template\Context             $context
     * @param ConfigProvider               $configProvider
     * @param \Astound\Affirm\Model\Config $configAffirm
     * @param array                        $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        \Astound\Affirm\Model\Config $configAffirm,
        array $data = []
    ) {
        if (isset($data['position']) && $data['position']) {
            $this->position = $data['position'];
        }

        $this->affirmPaymentConfig = $configAffirm;
        $this->configProvider = $configProvider;

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
        return $this->affirmPaymentConfig->isAslowasEnabled($position);
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
        if ($this->data && $this->getApr()  && $this->getLogo() && $this->getMonths()) {
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
        if ($this->getApr()  && $this->getLogo() && $this->getMonths()) {
            $this->setData('apr', $this->getApr());
            $this->setData('months', $this->getMonths());
            $this->setData('logo', $this->getLogo());

            $configProvider = $this->configProvider->getConfig();
            if ($configProvider['payment'][ConfigProvider::CODE]) {
                $config = $configProvider['payment'][ConfigProvider::CODE];
                $this->setData('script', $config['script']);
                $this->setData('public_api_key', $config['apiKeyPublic']);
            }
        }
    }

    /**
     * Get config data about saved in admin config month data.
     *
     * @return mixed
     */
    public function getMonths()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_aslowas/month');
    }

    /**
     * Get config data about saved in admin config the apr value.
     *
     * @return mixed
     */
    public function getApr()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_aslowas/apr_value');
    }

    /**
     * Get config data about saved affirm logo.
     *
     * @return mixed|string
     */
    public function getLogo()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_aslowas/logo');
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
        if ($this->validate()) {
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
