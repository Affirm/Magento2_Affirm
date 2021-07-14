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
use Astound\Affirm\Logger\Logger;

/**
 * Class Edit
 */
class Edit
{
    const CHARGE_ID = 'charge_id';
    const API_CHARGES_PATH = '/api/v2/charges/';


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
     * Affirm logging instance
     *
     * @var \Astound\Affirm\Logger\Logger
     */
    protected $logger;

    public function __construct(
        CollectionFactory $collectionFactory,
        ZendClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
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
            $chargeId = $order->getPayment()->getAdditionalInformation(self::CHARGE_ID);
            $newAddress = $order->getShippingAddress()->getData();
            $street = explode(PHP_EOL, $newAddress['street']);
            $url = $this->getApiUrl("{$chargeId}/update");
            $data = array(
                'shipping' => array(
                    'name' => array(
                        'full' => $newAddress['firstname'] . ' ' . $newAddress['lastname']
                    ),
                    'address' => array(
                        'line1' => $street[0],
                        'line2' => isset($street[1]) ? $street[1]: '' ,
                        'state' => $newAddress['region'],
                        'city' => $newAddress['city'],
                        'zipcode' => $newAddress['postcode'],
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
        $gateway = $this->scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox'
            ? \Astound\Affirm\Model\Config::API_URL_SANDBOX
            : \Astound\Affirm\Model\Config::API_URL_PRODUCTION;

        return trim($gateway, '/') . sprintf('%s%s', self::API_CHARGES_PATH, $additionalPath);
    }

    protected function isAffirmPaymentMethod($order)
    {
        return $order->getId() && $order->getPayment()->getMethod() == ConfigProvider::CODE;
    }

    protected function getPrivateApiKey()
    {
        return $this->scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox'
            ? $this->scopeConfig->getValue('payment/affirm_gateway/private_api_key_sandbox')
            : $this->scopeConfig->getValue('payment/affirm_gateway/private_api_key_production');
    }

    /**
     * Get public API key
     *
     * @return string
     */
    protected function getPublicApiKey()
    {
        return $this->scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox'
            ? $this->scopeConfig->getValue('payment/affirm_gateway/public_api_key_sandbox')
            : $this->scopeConfig->getValue('payment/affirm_gateway/public_api_key_production');
    }
}
