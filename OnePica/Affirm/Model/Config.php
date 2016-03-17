<?php
/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace OnePica\Affirm\Model;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

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
    /**#@-*/

    /**
     * Payment code
     *
     * @var string
     */
    protected $methodCode = 'affirm';

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
     * Inject scope config object
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param null|string $storeId
     *
     * @return mixed
     */
    protected function getConfigData($field, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->storeId;
        }
        $code = $this->methodCode;
        $path = 'payment/' . $code . '/' . $field;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Set store id
     *
     * @param $storeId
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
        return $this->getConfigData(self::KEY_PUBLIC_KEY_PRODUCTION);
    }

    /**
     * Get payment method mode
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->getConfigData(self::KEY_MODE);
    }

    /**
     * Return minimal order total
     *
     * @return mixed
     */
    public function getMinimumOrderTotal()
    {
        return $this->getConfigData(self::KEY_MINIMUM_ORDER_TOTAL);
    }

    /**
     * Return maximum order total
     *
     * @return mixed
     */
    public function getMaximumOrderTotal()
    {
        return $this->getConfigData(self::KEY_MAXIMUM_ORDER_TOTAL);
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKeyProduction()
    {
        return $this->getConfigData(self::KEY_PRIVATE_KEY_PRODUCTION);
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKeyProduction()
    {
        return $this->getConfigData(self::KEY_PUBLIC_KEY_PRODUCTION);
    }

    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKeyProduction()
    {
        return $this->getConfigData(self::KEY_FINANCIAL_PRODUCT_KEY_PRODUCTION);
    }

    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKeySandbox()
    {
        return $this->getConfigData(self::KEY_FINANCIAL_PRODUCT_KEY_SANDBOX);
    }

    /**
     * Get public key
     *
     * @return mixed
     */
    public function getPublicKeySandbox()
    {
        return $this->getConfigData(self::KEY_PUBLIC_KEY_SANDBOX);
    }

    /**
     * Return private api key
     *
     * @return mixed
     */
    public function getPrivateApiKeySandbox()
    {
        return $this->getConfigData(self::KEY_PRIVATE_KEY_SANDBOX);
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKeySandbox()
    {
        return $this->getConfigData(self::KEY_PUBLIC_KEY_SANDBOX);
    }

    /**
     * Retrieve api url sandbox
     *
     * @return mixed
     */
    public function getApiUrlSandbox()
    {
        return $this->getConfigData(self::KEY_API_URL_SANDBOX);
    }

    /**
     * Retrieve api url production
     *
     * @return mixed
     */
    public function getApiUrlProduction()
    {
        return $this->getConfigData(self::KEY_API_URL_PRODUCTION);
    }
}
