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

namespace Astound\Affirm\Helper;

use \Magento\Framework\Module\ResourceInterface;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Payment\Model\Method\Logger;
use Astound\Affirm\Model\Config as ConfigAffirm;

/**
 * Error tracker helper - sends error back to Affirm for monitoring
 *
 * @package Astound\Affirm\Helper
 */
class ErrorTracker
{

    /**#@+
     * Define constants
     */
    const POST = 'POST';
    const MAX_TRACE_FRAMES = 10;

    // The error_types accepted at the Affirm endpoint
    const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    const INVALID_AMOUNT = 'INVALID_AMOUNT';
    const TRANSACTION_DECLINED = 'TRANSACTION_DECLINED';

    const TRACKER_PATH = '/api/v1/partnersolutions/platform/tracker';
    /**#@-*/

    /**
     * Affirm config model
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmConfig;

    /**
     * @var AsyncClientInterface
     */
    private $httpClient;

    /**
     * Constructor
     * @param ResourceInterface $moduleResource
     * @param AsyncClientInterface $httpClient
     * @param ConfigAffirm $configAffirm
     */
    public function __construct(
        ResourceInterface $moduleResource,
        AsyncClientInterface $httpClient,
        ConfigAffirm $configAffirm
    ) {
        $this->moduleResource = $moduleResource;
        $this->httpClient = \Magento\Framework\App\ObjectManager::getInstance()->get(AsyncClientInterface::class);
        $this->affirmConfig = $configAffirm;
    }

    /**
     * Sends error information back to Affirm to be logged and monitored
     * If the exception is given, we will also send back information about the exception like the stack trace.
     * If the error message is not given, we default to the error type or (if given) the exception message.
     *
     * @param string $transaction_step
     * @param string $error_message
     * @param string $error_type
     * @param \Exception $exception
     */
    public function logErrorToAffirm(
        string $transaction_step,
        string $error_type,
        string $error_message=null,
        \Exception $exception=null
    )
    {
        // Form the object
        $extension_data = (object)[
            'platform'=>'magento',
            'environment'=>$this->affirmConfig->getMode() == 'sandbox' ? 'sandbox' : 'live',
            'language'=>'php',
            'code_version'=>phpversion(),
            'extension_version'=>$this->moduleResource->getDbVersion('Astound_Affirm')
        ];
        if ($exception) {
            $error_data = (object)[
                'error_type'=>$error_type,
                'error_message'=>$error_message ? $error_message : $exception->getMessage(),
                'error_class'=>get_class($exception),
                'trace'=>$this->formatStackTraces($exception)
            ];
        } else {
            $error_data = (object)[
                'error_type'=>$error_type,
                'error_message'=>$error_message ? $error_message : $error_type
            ];
        }

        $error_tracker_obj = (object)[
            "extension_data"=>$extension_data,
            "transaction_step"=>$transaction_step,
            "error_data"=>$error_data
        ];

        $gateway = $this->affirmConfig->getMode() == 'sandbox'
            ? \Astound\Affirm\Model\Config::API_URL_SANDBOX
            : \Astound\Affirm\Model\Config::API_URL_PRODUCTION;
        $tracker_endpoint = $gateway . self::TRACKER_PATH;

        $headers = [
            'Content-Type'=>'application/json',
            'Authorization'=>'Basic ' . base64_encode($this->affirmConfig->getPublicApiKey() . ':' . 
                $this->affirmConfig->getPrivateApiKey())
        ];

        // Send it and forget about it
        $response = $this->httpClient->request(
            new Request(
                $tracker_endpoint,
                self::POST,
                $headers,
                json_encode($error_tracker_obj)
            )
        );
    }

    /**
     * Format the stack traces for the custom endpoint
     * @param \Exception $exception
     * @return array
     */
    private function formatStackTraces(\Exception $exception)
    {
        $frames = array_slice($exception->getTrace(), 0, self::MAX_TRACE_FRAMES);

        $trace = [];
        foreach ($frames as $frame) {
            array_push($trace, (object)[
                "filename"=>$frame['file'],
                "lineno"=>$frame['line'],
                "method"=>$frame['function']
            ]);
        }

        return $trace;
    }
}
