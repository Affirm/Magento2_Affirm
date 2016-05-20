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
     * Config payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * @var
     */
    protected $configProvider;

    /**
     * Position of as low as block
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
        parent::__construct($context, $data);
    }

    /**
     * Verify if aslowas allowed in the position
     *
     * @param $position
     * @return int|mixed
     */
    protected function isAllowed($position)
    {
        return $this->affirmPaymentConfig->isAslowasEnabled($position);
    }

    /**
     * Get widget data
     *
     * @return string
     */
    public function getWidgetData()
    {
        if ($this->affirmPaymentConfig->isAslowasEnabled($this->position)) {
            $this->data['apr'] = $this->getApr()? $this->getApr(): 0;
            $this->data['months'] = $this->getMonths() ? $this->getMonths(): 0;
            $this->data['logo'] = $this->getLogo();

            $configProvider = $this->configProvider->getConfig();
            if ($configProvider['payment'][ConfigProvider::CODE]) {
                $config = $configProvider['payment'][ConfigProvider::CODE];
                $this->data['script'] = $config['script'];
                $this->data['public_api_key'] = $config['apiKeyPublic'];
            }
        }
        return json_encode($this->data);
    }

    /**
     * Get months configuration
     *
     * @return mixed
     */
    public function getMonths()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_aslowas/month');
    }

    /**
     * Get apr percents
     *
     * @return mixed
     */
    public function getApr()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_aslowas/apr_value');
    }

    /**
     * Get saved logo
     *
     * @return mixed|string
     */
    public function getLogo()
    {
        if ($this->_scopeConfig->getValue('affirm/affirm_aslowas/logo')) {
            return $this->_scopeConfig->getValue('affirm/affirm_aslowas/logo');
        }
        return '';
    }
}
