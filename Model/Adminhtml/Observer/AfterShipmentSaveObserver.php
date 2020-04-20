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

namespace Astound\Affirm\Model\Adminhtml\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\Method\Logger;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Laminas\Http\Client;

/**
 * Customer Observer Model
 */
class AfterShipmentSaveObserver implements ObserverInterface
{
    /**#@+
     * Define constants
     */
    const CHARGE_ID = 'charge_id';
    const API_CHARGES_PATH = '/api/v2/charges/';
    /**#@-*/

    /**
     * Order repository
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Client factory
     *
     * @var \Laminas\Http\Client
     */
    protected $httpClientFactory;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Construct
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param Client        $httpClientFactory
     * @param ScopeConfigInterface     $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Client $httpClientFactory,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Send call to Affirm
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $orderId = $shipment->getOrderId();
        $order = $this->orderRepository->get((int) $orderId);

        if ($this->isAffirmPaymentMethod($order)) {
            $tracks = $shipment->getTracks();
            $carriers = [];
            $confirmation = [];
            foreach ($tracks as $track) {
                $carriers[] = $track->getTitle();
                $confirmation[] = $track->getTrackNumber();
            }
            $shippingCarrier = implode(',', $carriers);
            $shippingConfirmation = implode(',', $confirmation);
            $orderIncrementId = $order->getIncrementId();
            $chargeId = $order->getPayment()->getAdditionalInformation(self::CHARGE_ID);

            $url = $this->getApiUrl("{$chargeId}/update");
            $data = [
                'order_id' => $orderIncrementId,
                'shipping_carrier' => $shippingCarrier,
                'shipping_confirmation' => $shippingConfirmation
            ];

            try {
                $client = $this->httpClientFactory;
                $client->setUri($url);
                $client->setAuth($this->getPublicApiKey(), $this->getPrivateApiKey());
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                $client->setEncType('application/json');
                $client->setRawBody($data);
                $client->setMethod('POST');
                $client->send();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * Is Affirm payment method
     *
     * @param $order
     * @return bool
     */
    protected function isAffirmPaymentMethod($order)
    {
        return $order->getId() && $order->getPayment()->getMethod() == ConfigProvider::CODE;
    }

    /**
     * Get private API key
     *
     * @return string
     */
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

    /**
     * Get API url
     *
     * @param string $additionalPath
     * @return string
     */
    protected function getApiUrl($additionalPath)
    {
        $gateway = $this->scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox'
            ? \Astound\Affirm\Model\Config::API_URL_SANDBOX
            : \Astound\Affirm\Model\Config::API_URL_PRODUCTION;

        return trim($gateway, '/') . sprintf('%s%s', self::API_CHARGES_PATH, $additionalPath);
    }
}
