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

namespace Astound\Affirm\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Astound\Affirm\Logger\Logger as AffirmLogger;

/**
 * Class ClientService
 */
class ClientService implements ClientInterface
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
     * Affirm logging instance
     *
     * @var AffirmLogger
     */
    protected $affirmLogger;

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
        AffirmLogger $affirmLogger,
        ConverterInterface $converter,
        ZendClientFactory $httpClientFactory
    ) {
        $this->logger = $logger;
        $this->affirmLogger = $affirmLogger;
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
        $log['uri'] = $transferObject->getUri();
        $response = [];
        try {
            /** @var \Magento\Framework\HTTP\ZendClient $client */
            $client = $this->httpClientFactory->create();
            $client->setUri($transferObject->getUri());
            $client->setAuth($transferObject->getAuthUsername(), $transferObject->getAuthPassword());
            if (!empty($transferObject->getBody())) {
                $data = $transferObject->getBody();
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                $client->setRawData($data, 'application/json');
            }

            $response = $client->request($transferObject->getMethod());
            $rawResponse = $response->getRawBody();
            $response = $this->converter->convert($rawResponse);
        } catch (\Exception $e) {
            $log['error'] = $e->getMessage();
            $this->logger->error($log);
            throw new ClientException(__($e->getMessage()));
        } finally {
            $log['response'] = $response;
            $this->logger->debug($log);
            $this->affirmLogger->debug('Astound\Affirm\Gateway\Http\Client\ClientService::placeRequest', $log);
        }

        return $response;
    }
}
