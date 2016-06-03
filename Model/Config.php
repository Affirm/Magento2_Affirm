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

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Config class
 *
 * @package Astound\Affirm\Model
 */
class Config
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
    /**#@-*/

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
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get config data
     *
     * @param      $field
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
     * Get public key
     *
     * @return mixed
     */
    public function getPublicKeyProduction()
    {
        return $this->getConfigData(
            self::KEY_PUBLIC_KEY_PRODUCTION,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get payment method mode
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->getConfigData(
            self::KEY_MODE,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return minimal order total
     *
     * @return mixed
     */
    public function getMinimumOrderTotal()
    {
        return $this->getConfigData(
            self::KEY_MINIMUM_ORDER_TOTAL,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return maximum order total
     *
     * @return mixed
     */
    public function getMaximumOrderTotal()
    {
        return $this->getConfigData(
            self::KEY_MAXIMUM_ORDER_TOTAL,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKeyProduction()
    {
        return $this->getConfigData(
            self::KEY_PRIVATE_KEY_PRODUCTION,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKeyProduction()
    {
        return $this->getConfigData(
            self::KEY_PUBLIC_KEY_PRODUCTION,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKeyProduction()
    {
        return $this->getConfigData(
            self::KEY_FINANCIAL_PRODUCT_KEY_PRODUCTION,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }
    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKeySandbox()
    {
        return $this->getConfigData(
            self::KEY_FINANCIAL_PRODUCT_KEY_SANDBOX,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKeySandbox()
    {
        return $this->getConfigData(
            self::KEY_PRIVATE_KEY_SANDBOX,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKeySandbox()
    {
        return $this->getConfigData(
            self::KEY_PUBLIC_KEY_SANDBOX,
            $this->getCurrentWebsiteId(),
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve api url sandbox
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return ($this->getMode() == 'sandbox')?
            $this->getConfigData(
                self::KEY_API_URL_SANDBOX,
                $this->getCurrentWebsiteId(),
                ScopeInterface::SCOPE_WEBSITE
            ):
            $this->getConfigData(
                self::KEY_API_URL_PRODUCTION,
                $this->getCurrentWebsiteId(),
                ScopeInterface::SCOPE_WEBSITE
            );
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
            if ($this->getConfigData('mode') == 'sandbox') {
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
     * @param string $section
     *
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
            ScopeInterface::SCOPE_WEBSITE,
            $this->getCurrentWebsiteId()
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
            $this->getCurrentWebsiteId()
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
                'affirm/affirm_promo/promo_key',
                ScopeInterface::SCOPE_WEBSITE,
                $this->getCurrentWebsiteId()
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
}
