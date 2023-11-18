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
use Astound\Affirm\Gateway\Helper\Request\Action;
use Astound\Affirm\Gateway\Helper\Util;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Laminas\Http\Client;
use Astound\Affirm\Logger\Logger as AffirmLogger;
use \Magento\Framework\Module\ResourceInterface;
use Astound\Affirm\Helper\ErrorTracker;

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
     * @var \Laminas\Http\Client
     */
    protected $httpClientFactory;

    /**
     * Action
     *
     * @var Action
     */
    protected $action;

    /**
     * Util
     *
     * @var Util
     */
    protected $util;

    /**
     * Error Tracker
     *
     * @var ErrorTracker
     */
    protected $errorTracker;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param ConverterInterface $converter,
     * @param Client $httpClientFactory
     * @param Action $action
     * @param ResourceInterface $moduleResource
     * @param ErrorTracker $error_tracker
     */
    public function __construct(
        Logger $logger,
        AffirmLogger $affirmLogger,
        ConverterInterface $converter,
        Client $httpClientFactory,
        Action $action,
        Util $util,
        ResourceInterface $moduleResource,
        ErrorTracker $errorTracker
    ) {
        $this->logger = $logger;
        $this->affirmLogger = $affirmLogger;
        $this->converter = $converter;
        $this->httpClientFactory = $httpClientFactory;
        $this->action = $action;
        $this->util = $util;
        $this->moduleResource = $moduleResource;
        $this->errorTracker = $errorTracker;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array|\Http_Response
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [];
        $response = [];
        $requestUri = trim($transferObject->getUri(), '/');
        try {
            $client = $this->httpClientFactory;
            $client->setUri($requestUri);
            $client->setAuth($transferObject->getAuthUsername(), $transferObject->getAuthPassword());
            $headers = $transferObject->getHeaders();
            if (strpos($transferObject->getUri(), $this->action::API_TRANSACTIONS_PATH) !== false) {
                $idempotencyKey = $this->util->generateIdempotencyKey();
                $headers[Util::IDEMPOTENCY_KEY] = $idempotencyKey;
            }
            $client->setHeaders($headers);
            if (!empty($transferObject->getBody())) {
                $data = $transferObject->getBody();
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                $client->setEncType('application/json');
                $client->setRawBody($data);
            }
            $client->setMethod($transferObject->getMethod());
            $response = $client->send();
            $rawResponse = $response->getBody();
            $response = $this->converter->convert($rawResponse);
        } catch (\Exception $e) {
            $log['error'] = $e->getMessage();
            $this->logger->debug($log);

            // Get transaction step
            if (strpos($transferObject->getUri(), $this->action::API_CHECKOUT_PATH) !== false) {
                $transaction_step = 'pre_auth';
            } else {
                $last_section = substr(strrchr($transferObject->getUri(), '/'), 1);
                $transaction_step = $last_section ? $last_section : 'auth';
            }

            $this->errorTracker->logErrorToAffirm(
                transaction_step: $transaction_step,
                error_type: ErrorTracker::INTERNAL_SERVER_ERROR,
                exception: $e
            );

            throw new ClientException(__($e->getMessage()));
        } finally {
            $log['uri'] = $requestUri;
            $log['response'] = $response;
            $this->logger->debug($log);
            $this->affirmLogger->debug('Astound\Affirm\Gateway\Http\Client\ClientService::placeRequest', $log);
        }

        return $response;
    }
}
