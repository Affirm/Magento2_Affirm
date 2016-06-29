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

namespace Astound\Affirm\Model;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency;

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
    const KEY_FINANCIAL_PRODUCT_KEY_SANDBOX = 'financial_product_key_sandbox';
    const KEY_PUBLIC_KEY_PRODUCTION = 'public_api_key_production';
    const KEY_PRIVATE_KEY_PRODUCTION = 'private_api_key_production';
    const KEY_FINANCIAL_PRODUCT_KEY_PRODUCTION = 'financial_product_key_production';
    const KEY_MINIMUM_ORDER_TOTAL = 'minimum_order_total';
    const KEY_MAXIMUM_ORDER_TOTAL = 'maximum_order_total';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_API_URL_SANDBOX = 'api_url_sandbox';
    const KEY_API_URL_PRODUCTION = 'api_url_production';
    const METHOD_BML = 'affirm_promo';
    const KEY_ASLOWAS = 'affirm_aslowas';
    const CURRENCY_CODE = 'USD';
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
        'financial_product_key_production' => true,
        'public_key_production' => true,
        'private_key_production' => true,
        'maximum_order_total' => true,
        'minimum_order_total' => true,
        'api_url_production' => true,
        'api_url_sandbox' => true
    ];

    /**
     * Inject scope and store manager object
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, Currency $currency)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
    }

    /**
     * Get config data
     *
     * @param $field
     * @param null $id
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
     * Is turn off functionality
     */
    public function isTurnOffFunctionality()
    {
        return $this->getValue('turn_off_for_non_dollar_currency') && !$this->isCurrencyValid();
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
     * Is current currency valid
     *
     * @return bool
     */
    public function isCurrentCurrencyValid()
    {
        $currentCurrency = $this->storeManager->getStore()
            ->getCurrentCurrencyCode();
        $isValid = true;
        if ($currentCurrency != self::CURRENCY_CODE) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * Get currency rates
     *
     * @return bool
     */
    public function getCurrencyRates()
    {
        $currentStore = $this->getCurrentStore();
        $currencyCode = $currentStore->getCurrentCurrencyCode();
        $rates = $this->currency->getCurrencyRates('USD', $currencyCode);
        return isset($rates[$currencyCode]) ? $rates[$currencyCode] : false;
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
     * Get payment method mode
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->getValue('mode');
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKey()
    {
        return ($this->getValue('mode') == 'sandbox') ?
            $this->getValue(self::KEY_PRIVATE_KEY_SANDBOX):
            $this->getValue(self::KEY_PRIVATE_KEY_PRODUCTION);
    }

    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKey()
    {
        return ($this->getValue('mode') == 'sandbox') ?
            $this->getValue(self::KEY_FINANCIAL_PRODUCT_KEY_SANDBOX):
            $this->getValue(self::KEY_FINANCIAL_PRODUCT_KEY_PRODUCTION);
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKey()
    {
        return ($this->getValue('mode') == 'sandbox')?
            $this->getValue(self::KEY_PUBLIC_KEY_SANDBOX):
            $this->getValue(self::KEY_PUBLIC_KEY_PRODUCTION);
    }

    /**
     * Retrieve api url sandbox
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return ($this->getMode() == 'sandbox')?
            $this->getValue(self::KEY_API_URL_SANDBOX):
            $this->getValue(self::KEY_API_URL_PRODUCTION);
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
            if ($this->getValue('mode') == 'sandbox') {
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
     * Get promo key
     *
     * @return mixed
     */
    public function getPromoKey()
    {
        return $this->scopeConfig
            ->getValue(
                'affirm/' . self::METHOD_BML . '/promo_key',
                ScopeInterface::SCOPE_WEBSITE,
                $this->getWebsiteId()
            );
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
     * Aslow as activation flag
     *
     * @param $position
     * @return int|mixed
     */
    public function isAsLowAsEnabled($position)
    {
        $flag = $this->scopeConfig->getValue(
            'affirm/' . self::KEY_ASLOWAS . '/' . 'enabled_' . $position,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $flag ? $flag: 0;
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
        if ($path !== null) {
            $value = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_WEBSITE
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
}
