<?php
/**
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */


namespace Astound\Affirm\Block\Promotion;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Astound\Affirm\Model\Config;
use Astound\Affirm\Helper\Payment;
use Astound\Affirm\Helper;

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
     * @var Config
     */
    protected $affirmPaymentConfig;

    /**
     * Affirm payment model instance
     *
     * @var Payment
     */
    protected $helper;

    /**
     * Financing program helper factory
     *
     * @var Helper\FinancingProgram
     */
    protected $fpHelper;

    /**
     * AsLowAs helper factory
     *
     * @var Helper\AsLowAs
     */
    protected $alaHelper;

    /**
     * Inject all needed objects
     *
     * @param Template\Context $context
     * @param Config           $configAffirm
     * @param ConfigProvider   $configProvider
     * @param Payment          $helper
     * @param array            $data
     * @param Helper\FinancingProgram $fpHelper
     * @param Helper\AsLowAs $alaHelper
     */
    public function __construct(
        Template\Context $context,
        Config $configAffirm,
        ConfigProvider $configProvider,
        Payment $helper,
        Helper\FinancingProgram $fpHelper,
        Helper\AsLowAs $alaHelper,
        array $data = []
    ) {
        $this->affirmPaymentConfig = $configAffirm;
        $this->helper = $helper;
        $this->position = isset($data['position']) ? $data['position']: '';
        $this->section = isset($data['section']) ? $data['section']: 0;
        $this->configProvider = $configProvider;
        $this->fpHelper = $fpHelper;
        $this->alaHelper = $alaHelper;
        parent::__construct($context, $data);
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
     * Check if current page is cart page
     *
     * @return bool
     */
    protected function isCartPage()
    {
        return $this->section === 'checkout_cart';
    }

    /**
     * Check if current page is product page
     *
     * @return bool
     */
    protected function isProductPage()
    {
        return $this->section === 'product';
    }

    /**
     * Check if current page is category page
     *
     * @return bool
     */
    protected function isCategoryPage()
    {
        return $this->section === 'category';
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

        if ($this->isCartPage() && !$this->helper->isAffirmAvailable()) {
            return '';
        }

        if ($this->isProductPage() && !$this->helper->isAffirmAvailableForProduct()) {
            return '';
        }

        if ( $this->affirmPaymentConfig->getBmlSize($this->section) == '"Pay over time banner sizes"' || $this->affirmPaymentConfig->getBmlSize($this->section) == '"Make Monthly Payments banner sizes"'  ) {
            return '';
        }

        $this->processContainer($this->section);
        $this->setSize($this->affirmPaymentConfig->getBmlSize($this->section));
        $this->setAffirmAssetsUrl($this->affirmPaymentConfig->getAffirmAssetsUrl());
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
        if ($this->isProductPage()) {
            $options['backorders_options'] = $this->helper->getConfigurableProductBackordersOptions();
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

    /**
     * get MFP value
     * @return string
     */
    public function getMFPValue()
    {
        $dynamicallyMFPValue = $this->fpHelper->getCustomerFinancingProgram();
        if (!empty($dynamicallyMFPValue)) {
            return $dynamicallyMFPValue;
        } elseif ($this->isProductPage()) {
            $productCollection = $this->helper->getProduct()->getCollection()
                ->addAttributeToSelect(['affirm_product_promo_id', 'affirm_product_mfp_type', 'affirm_product_mfp_priority', 'affirm_product_mfp_start_date', 'affirm_product_mfp_end_date'])
                ->addAttributeToFilter('entity_id', $this->helper->getProduct()->getId());

            return $this->alaHelper->getFinancingProgramValueALS($productCollection);
        } elseif ($this->isCartPage()) {
            return $this->alaHelper->getFinancingProgramValue();
        } else {
            return $this->fpHelper->getFinancingProgramValue();
        }
    }

    /**
     * Get Page type
     *
     * @return string
     */
    public function getPageType()
    {
        if ($this->isCartPage()) {
            return 'cart';
        } else if ($this->isCategoryPage()) {
            return 'category';
        } else if ($this->isProductPage()) {
            return 'product';
        } else {
            return 'homepage';
        }
    }
}
