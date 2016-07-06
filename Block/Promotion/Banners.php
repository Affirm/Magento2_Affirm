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

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Astound\Affirm\Model\Config;

/**
 * Class Banner
 *
 * @package Astound\Affirm\Block\Promotion
 */
class Banners extends \Magento\Framework\View\Element\Template
{
    /**
     * Start tag for html container
     *
     * @var string
     */
    protected $startTag;

    /**
     * Ended tag for html container
     *
     * @var string
     */
    protected $endTag;

    /**
     * Section in which the banner will be visible
     *
     * @var string
     */
    protected $section;

    /**
     * Position of the banner
     *
     * @var string
     */
    protected $position;

    /**
     * Config payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Inject all needed objects
     *
     * @param Template\Context $context
     * @param Config           $configAffirm
     * @param ConfigProvider   $configProvider
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        \Astound\Affirm\Model\Config $configAffirm,
        \Astound\Affirm\Model\Ui\ConfigProvider $configProvider,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->affirmPaymentConfig = $configAffirm;
        $this->position = isset($data['position']) ? $data['position']: '';
        $this->section = isset($data['section']) ? $data['section']: 0;
        $this->configProvider = $configProvider;
    }

    /**
     * Get promo key from
     *
     * @return mixed
     */
    protected function getPublisherId()
    {
        return $this->_scopeConfig->getValue(
            'affirm/affirm_promo/promo_key',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get is promo active
     *
     * @return mixed
     */
    protected function getIsActive()
    {
        return $this->_scopeConfig->getValue(
            'affirm/affirm_promo/enabled',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Verify block before render html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getIsActive() || !$this->affirmPaymentConfig->isCurrencyValid()) {
                return '';
        }
        $display  = $this->affirmPaymentConfig->getBmlDisplay($this->section);
        $position = $this->affirmPaymentConfig->getBmlPosition($this->section);

        if (!$display) {
            return '';
        }

        if ($this->position != $position) {
            return '';
        }

        $this->processContainer($this->section);
        $this->setPromoKey($this->affirmPaymentConfig->getPromoKey());
        $this->setSize($this->affirmPaymentConfig->getBmlSize($this->section));
        return parent::_toHtml();
    }

    /**
     * Get options for
     *
     * @return string
     */
    public function getOptions()
    {
        $options = [];
        $configProvider = $this->configProvider->getConfig();
        if ($configProvider['payment'][ConfigProvider::CODE]) {
            $config = $configProvider['payment'][ConfigProvider::CODE];
            if ($config && isset($config['script']) && isset($config['apiKeyPublic'])) {
                $options['script'] = $config['script'];
                $options['public_api_key'] = $config['apiKeyPublic'];
            }
        }
        return json_encode($options);
    }

    /**
     * Process container
     *
     * @param $section
     */
    protected function processContainer($section)
    {
        $container = $this->affirmPaymentConfig
            ->getHtmlContainer($section);
        if ($container) {
            $containerParts = explode('{container}', $container);
            if ($containerParts && is_array($containerParts)) {
                // Get open tag for container
                $this->startTag = current($containerParts);
                // Get close tag for the container
                $this->endTag = end($containerParts);
            }
        }
    }

    /**
     * Get start container Tag
     *
     * @return string
     */
    public function getStartContainerTag()
    {
        return $this->startTag ? $this->startTag: '';
    }

    /**
     * Get end container tag
     *
     * @return string
     */
    public function getEndContainerTag()
    {
        return $this->endTag ? $this->endTag: '';
    }
}
