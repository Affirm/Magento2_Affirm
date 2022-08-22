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

namespace Astound\Affirm\Model\Plugin\Order\AddressSave;

use Magento\Sales\Controller\Adminhtml\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Astound\Affirm\Logger\Logger;

/**
 * Class Edit
 */
class Edit
{
    const TRANSACTION_ID = 'transaction_id';
    const CHARGE_ID = 'charge_id';
    const API_TRANSACTIONS_PATH = '/api/v1/transactions/';


    /**
     * Collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Client factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $collectionFactory
     */

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\App\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Affirm logging instance
     *
     * @var \Astound\Affirm\Logger\Logger
     */
    protected $logger;

    public function __construct(
        CollectionFactory $collectionFactory,
        ZendClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Plugin for edit order address in admin
     *
     * @param Address $controller
     * @param callable   $method
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute($controller , $method)
    {

            $addressId = $controller->getRequest()->getParam('address_id');
            $orderCollection = $this->_collectionFactory->create()->addAttributeToSearchFilter(
                [
                    ['attribute' => 'billing_address_id', 'eq' => $addressId . '%'],
                    ['attribute' => 'shipping_address_id', 'eq' => $addressId . '%']
                ]
            )->load();

            $order = $orderCollection->getFirstItem();
        if ($this->isAffirmPaymentMethod($order)) {
            $transactionId = $order->getPayment()->getAdditionalInformation(self::TRANSACTION_ID) ?:
                $order->getPayment()->getAdditionalInformation(self::CHARGE_ID);
            $newAddress = $order->getShippingAddress()->getData();
            $street = explode(PHP_EOL, $newAddress['street']);
            $url = $this->getApiUrl("{$transactionId}");
            $data = array(
                'shipping' => array(
                    'name' => array(
                        'full' => $newAddress['firstname'] . ' ' . $newAddress['lastname']
                    ),
                    'address' => array(
                        'street1' => $street[0],
                        'street2' => isset($street[1]) ? $street[1]: '' ,
                        'region1_code' => $newAddress['region'],
                        'city' => $newAddress['city'],
                        'postal_code' => $newAddress['postcode'],
                        'country' => $newAddress['country_id']
                    )
                )
            );

            $log = [];
            $log['data'] = $data;
            $log['url'] = $url;

            try {
                $client = $this->httpClientFactory->create();
                $client->setUri($url);
                $client->setAuth($this->getPublicApiKey(), $this->getPrivateApiKey());
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                $client->setRawData($data, 'application/json');
                $response = $client->request('POST');
                $responseBody = $response->getBody();
                $log['response'] = json_decode($responseBody, true);
            } catch (\Exception $e) {
                $log['error'] = $e->getMessage();
            } finally {
                $this->logger->debug('Astound\Affirm\Model\Plugin\Order\AddressSave\Edit::afterExecute', $log);
            }
        }

        return $method;
    }

    protected function getApiUrl($additionalPath)
    {
        $gateway = $this->getIsSandboxMode()
            ? \Astound\Affirm\Model\Config::API_URL_SANDBOX
            : \Astound\Affirm\Model\Config::API_URL_PRODUCTION;

        return trim($gateway, '/') . sprintf('%s%s', self::API_TRANSACTIONS_PATH, $additionalPath);
    }

    protected function isAffirmPaymentMethod($order)
    {
        return $order->getId() && $order->getPayment()->getMethod() == ConfigProvider::CODE;
    }

    protected function getPrivateApiKey()
    {
        return $this->getIsSandboxMode()
            ? $this->scopeConfig->getValue('payment/affirm_gateway/private_api_key_sandbox', ScopeInterface::SCOPE_STORE, $this->getStoreId())
            : $this->scopeConfig->getValue('payment/affirm_gateway/private_api_key_production', ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    /**
     * Get public API key
     *
     * @return string
     */
    protected function getPublicApiKey()
    {
        return $this->getIsSandboxMode()
            ? $this->scopeConfig->getValue('payment/affirm_gateway/public_api_key_sandbox', ScopeInterface::SCOPE_STORE, $this->getStoreId())
            : $this->scopeConfig->getValue('payment/affirm_gateway/public_api_key_production', ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    /**
     * Get is sandbox mode
     *
     * @return boolean
     */
    protected function getIsSandboxMode()
    {
        return $this->scopeConfig->getValue('payment/affirm_gateway/mode', ScopeInterface::SCOPE_STORE, $this->getStoreId()) == 'sandbox';
    }

    /**
     * Get store id
     *
     * @return string
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
