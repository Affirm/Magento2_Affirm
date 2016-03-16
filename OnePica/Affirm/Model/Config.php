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
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const KEY_FINANCIAL_PRODUCT_KEY = 'financial_product_key';
    const KEY_MINIMUM_ORDER_TOTAL = 'minimum_order_total';
    const KEY_MAXIMUM_ORDER_TOTAL = 'maximum_order_total';
    const KEY_SORT_ORDER = 'sort_order';
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
        'financial_product_key' => true,
        'public_key' => true,
        'private_key' => true,
        'maximum_order_total' => true,
        'minimum_order_total' => true,
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
    public function getPublicKey()
    {
        return $this->getConfigData(self::KEY_PUBLIC_KEY);
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
    public function getPrivateApiKey()
    {
        return $this->getConfigData(self::KEY_PRIVATE_KEY);
    }

    /**
     * Return public api key
     *
     * @return mixed
     */
    public function getPublicApiKey()
    {
        return $this->getConfigData(self::KEY_PUBLIC_KEY);
    }

    /**
     * Return financial product key
     *
     * @return mixed
     */
    public function getFinancialProductKey()
    {
        return $this->getConfigData(self::KEY_FINANCIAL_PRODUCT_KEY);
    }
}
