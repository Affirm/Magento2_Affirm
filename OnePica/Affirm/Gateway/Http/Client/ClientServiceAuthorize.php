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

namespace OnePica\Affirm\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use OnePica\Affirm\Gateway\Helper\Util;

class ClientServiceAuthorize implements ClientInterface
{
    /**#@+
     * Define constants
     */
    const GET     = 'GET';
    const POST    = 'POST';
    /**#@-*/

    /**
     * Logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Converter
     *
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * Client factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param ConverterInterface $converter,
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Logger $logger,
        ConverterInterface $converter,
        ZendClientFactory $httpClientFactory
    ) {
        $this->logger = $logger;
        $this->converter = $converter;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array|\Zend_Http_Response
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [];
        $response = [];
        try {
            /** Get total before authorize **/
            /** @var \Magento\Framework\HTTP\ZendClient $client */
            $client = $this->httpClientFactory->create();
            $client->setUri($transferObject->getBody()['url']);
            $client->setAuth($transferObject->getAuthUsername(), $transferObject->getAuthPassword());
            $response = $client->request(self::GET);
            $rawResponse = $response->getRawBody();
            $response = $this->converter->convert($rawResponse);
            if ($response['total'] != Util::formatToCents($transferObject->getBody()['amount'])) {
                return $response;
            }

            /** API authorize call **/
            /** @var \Magento\Framework\HTTP\ZendClient $client */
            $client = $this->httpClientFactory->create();
            $client->setUri($transferObject->getUri());
            $client->setAuth($transferObject->getAuthUsername(), $transferObject->getAuthPassword());
            $requestBody = json_encode($transferObject->getBody()['body'], JSON_UNESCAPED_SLASHES);
            $client->setRawData($requestBody, 'application/json');
            $response = $client->request($transferObject->getMethod());
            $rawResponse = $response->getRawBody();
            $response = $this->converter->convert($rawResponse);
        } catch (\Exception $e) {
            throw new ClientException(__($e->getMessage()));
        } finally {
            $log['response'] = $response;
            $this->logger->debug($log);
        }

        return $response;
    }
}
