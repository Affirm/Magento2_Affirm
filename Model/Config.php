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

namespace Astound\Affirm\Model;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Config class
 *
 * @package Astound\Affirm\Model
 */
class Config implements ConfigInterface
{
    /**#@+
     * Define constants
     */
    const KEY_ACTIVE = 'active';
    const KEY_MODE = 'mode';
    const KEY_PUBLIC_KEY_SANDBOX = 'public_api_key_sandbox';
    const KEY_PRIVATE_KEY_SANDBOX = 'private_api_key_sandbox';
    const KEY_PUBLIC_KEY_PRODUCTION = 'public_api_key_production';
    const KEY_PRIVATE_KEY_PRODUCTION = 'private_api_key_production';
    const KEY_MINIMUM_ORDER_TOTAL = 'minimum_order_total';
    const KEY_MAXIMUM_ORDER_TOTAL = 'maximum_order_total';
    const KEY_SORT_ORDER = 'sort_order';
    const API_URL_SANDBOX = 'https://sandbox.affirm.com';
    const API_URL_PRODUCTION = 'https://api.affirm.com';
    const METHOD_BML = 'affirm_promo';
    const KEY_ASLOWAS = 'affirm_aslowas';
    const KEY_MFP = 'affirm_mfp';
    const CURRENCY_CODE = 'USD';
    const KEY_ASLOWAS_DEVELOPER = 'affirm_aslowas_developer';
    const KEY_PIXEL = 'affirm_pixel';
    /**#@-*/

    /**
     * Website Id
     *
     * @var int
     */
    protected $websiteId;

    /**
     * Payment code
     *
     * @var string
     */
    protected $methodCode = 'affirm_gateway';

    /**
     * Scope configuration object
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Current store id
     *
     * @var int
     */
    protected $storeId;

    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Path pattern
     *
     * @var $pathPattern
     */
    protected $pathPattern;

    /**
     * Currency
     *
     * @var $currency
     */
    protected $currency;

    /**
     * Permissions to config fields
     *
     * @var array
     */
    protected $affirmSharedConfigFields = [
        'active' => true,
        'mode' => true,
        'public_key_production' => true,
        'private_key_production' => true,
        'maximum_order_total' => true,
        'minimum_order_total' => true,
        'api_url_production' => true,
        'api_url_sandbox' => true
    ];

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * Inject scope and store manager object
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Currency              $currency
     * @param TaxConfig             $taxConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Currency $currency,
        TaxConfig $taxConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Get config data
     *
     * @param        $field
     * @param null   $id
     * @param string $scope
     * @return mixed
     */
    public function getConfigData($field, $id = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->methodCode) {
            $path = 'payment/' . $this->methodCode . '/' . $field;
            $res = $this->scopeConfig->getValue($path, $scope, $id);
            return $res;
        }
        return false;
    }

    /**
     * Is base currency valid
     *
     * @return bool
     */
    public function isCurrencyValid()
    {
        $currentCurrency = $this->storeManager->getStore()
            ->getBaseCurrencyCode();
        $isValid = true;
        if ($currentCurrency != self::CURRENCY_CODE) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * Is current currency USD
     *
     * @return bool
     */
    public function isCurrentStoreCurrencyUSD()
    {
        $currentCurrency = $this->storeManager->getStore()
            ->getCurrentCurrencyCode();
        $isUSD = true;
        if ($currentCurrency != self::CURRENCY_CODE) {
            $isUSD = false;
        }
        return $isUSD;
    }

    /**
     * Get USD currency rate for current currency
     *
     * @return bool
     */
    public function getUSDCurrencyRate()
    {
        $currentStore = $this->getCurrentStore();
        $currencyCode = $currentStore->getCurrentCurrencyCode();
        $rate = $this->currency->getCurrencyRates('USD', $currencyCode);
        return isset($rate[$currencyCode]) ? $rate[$currencyCode] : false;
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    protected function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }


    /**
     * Get payment method environment mode
     *
     * @return mixed
     */
    public function getMode()
    {
        $storeId = $this->getCurrentStoreId();
        return $this->getValue(self::KEY_MODE, $storeId);
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKey()
    {
        $storeId = $this->getCurrentStoreId();
        return ($this->getMode() == 'sandbox') ?
            $this->getValue(self::KEY_PRIVATE_KEY_SANDBOX, $storeId) :
            $this->getValue(self::KEY_PRIVATE_KEY_PRODUCTION, $storeId);
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKey()
    {
        $storeId = $this->getCurrentStoreId();
        return ($this->getMode() == 'sandbox') ?
            $this->getValue(self::KEY_PUBLIC_KEY_SANDBOX, $storeId) :
            $this->getValue(self::KEY_PUBLIC_KEY_PRODUCTION, $storeId);
    }

    /**
     * Retrieve api url sandbox
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return ($this->getMode() == 'sandbox') ?
            self::API_URL_SANDBOX :
            self::API_URL_PRODUCTION;
    }

    /**
     * Get script uri
     *
     * @return string
     */
    public function getScript()
    {
        $apiUrl = $this->getApiUrl();
        $prefix = "cdn1";
        if ($apiUrl) {
            if ($this->getMode() == 'sandbox') {
                $pattern = '~(http|https)://~';
                $replacement = '-';
            } else {
                $pattern = '~(http|https)://api~';
                $replacement = '';
            }
            $apiString = preg_replace($pattern, $replacement, $apiUrl);
            $result = 'https://' . $prefix . $apiString . '/js/v2/affirm.js';
            return $result;
        }
        return '';
    }

    /**
     * Get Display option from stored config
     *
     * @param string $section
     * @return mixed
     */
    public function getBmlDisplay($section)
    {
        $display = $this->scopeConfig->getValue(
            'affirm/' . self::METHOD_BML . '_' . $section . '/' . 'display',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $display ? $display : 0;
    }

    /**
     * Get html container info
     *
     * @param $section
     * @return int|mixed
     */
    public function getHtmlContainer($section)
    {
        $container = $this->scopeConfig->getValue(
            'affirm/' . 'affirm_developer' . '/' . $section . '_container',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $container ? $container : 0;
    }

    /**
     * Get Bml position
     *
     * @param $section
     * @return int|mixed
     */
    public function getBmlPosition($section)
    {
        $position = $this->scopeConfig->getValue(
            'affirm/' . self::METHOD_BML . '_' . $section . '/' . 'position',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $position ? $position : 0;
    }

    /**
     * Get Bml size
     *
     * @param $section
     * @return int|mixed
     */
    public function getBmlSize($section)
    {
        $size = $this->scopeConfig->getValue(
            'affirm/' . self::METHOD_BML . '_' . $section . '/' . 'size',
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
        return $size ? $size : 0;
    }

    /**
     * Get ALA placement
     *
     * @return int|mixed
     */
    public function getAlaPlacement()
    {
        $placement = $this->scopeConfig->getValue(
            'affirm/'. self::KEY_ASLOWAS_DEVELOPER .'/placement',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $placement ? $placement : 0;
    }

    /**
     * Get current website id
     *
     * @return int
     */
    protected function getCurrentWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Is disabled for backordered items flag
     *
     * @return bool
     */
    public function isDisabledForBackorderedItems()
    {
        return (bool)$this->getConfigData('disable_for_backordered_items');
    }

    /**
     * Aslow as activation flag
     *
     * @param Astound\Affirm\Model\Entity\Attribute\Source\FinancingProgramType$position
     * @return int|mixed
     */
    public function isAsLowAsEnabled($position)
    {
        $flag = $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'enabled_' . $position,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $flag ? $flag : 0;
    }

    /**
     * Get As Low As apr
     *
     * @return int
     */
    public function getAsLowAsApr()
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'apr_value', ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get config data about saved in admin config month data.
     *
     * @return int
     */
    public function getAsLowAsMonths()
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'month', ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get config data about saved affirm logo.
     *
     * @return mixed|string
     */
    public function getAsLowAsLogo()
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'logo', ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get config data "Minimum amount for displaying the monthly payment pricing".
     *
     * @return float
     */
    public function getAsLowAsMinMpp()
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'min_mpp', ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get AsLowAs config value
     *
     * @return float
     */
    public function getAsLowAsValue($key)
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . $key, ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**Get mfp config value
     *
     * @param string $key
     * @return mixed
     */
    public function getMfpValue($key)
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_MFP . '/' . $key, ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**Get pixel config value
     *
     * @param string $key
     * @return mixed
     */
    public function getPixelValue($key)
    {
        return $this->scopeConfig->getValue(
            'affirm/' . self::KEY_PIXEL . '/' . $key, ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Returns payment configuration value
     *
     * @param string $key
     * @param null   $storeId
     * @return mixed
     */
    public function getValue($key, $storeId = null)
    {
        $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
        $path = $this->_getSpecificConfigPath($underscored);
        $storeScope = !empty($storeId) ? ScopeInterface::SCOPE_STORE : ScopeInterface::SCOPE_WEBSITE;
        if ($path !== null) {
            $value = $this->scopeConfig->getValue(
                $path,
                $storeScope,
                $storeId
            );
            return $value;
        }
        return false;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        if ($this->pathPattern) {
            return sprintf($this->pathPattern, $this->methodCode, $fieldName);
        }

        return "payment/{$this->methodCode}/{$fieldName}";
    }

    /**
     * Set website id
     *
     * @param $websiteId
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;
    }

    /**
     * Get website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
    }

    /**
     * Get all As Low As config
     *
     * @return array
     */
    public function getAllAsLowAsConfig()
    {
        return [
            'asLowAsActiveMiniCart' => $this->getConfigData('active') && $this->isAslowasEnabled('mcc') &&
                $this->isCurrencyValid(),
            'apr' => $this->getAsLowAsApr(),
            'months' => $this->getAsLowAsMonths(),
            'logo' => $this->getAsLowAsLogo(),
            'script' => $this->getScript(),
            'public_api_key' => $this->getPublicApiKey(),
            'min_order_total' => $this->getConfigData('min_order_total'),
            'max_order_total' => $this->getConfigData('max_order_total'),
            'currency_rate' => !$this->isCurrentStoreCurrencyUSD() ? $this->getUSDCurrencyRate() : null,
            'display_cart_subtotal_incl_tax' => (int)$this->taxConfig->displayCartSubtotalInclTax(),
            'display_cart_subtotal_excl_tax' => (int)$this->taxConfig->displayCartSubtotalExclTax()
        ];
    }

    /**
     * Get assets url
     *
     * @return string
     */
    public function getAffirmAssetsUrl()
    {
        $prefix = "cdn-assets";
        $domain = "affirm.com";
        $assetPath = "images/banners";
        return 'https://' . $prefix . '.' . $domain . '/' . $assetPath ;
    }

    /**
     * Get checkout flow type
     *
     * @return mixed
     */
    public function getCheckoutFlowType()
    {
        return $this->getConfigData('checkout_flow_type');
    }

    /**
     * Get partial capture
     *
     * @return bool
     */
    public function getPartialCapture()
    {
        return $this->getConfigData('partial_capture');
    }
}
