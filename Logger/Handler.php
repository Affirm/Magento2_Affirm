<?php
namespace Astound\Affirm\Logger;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger;

class Handler extends BaseHandler
{
    /**
     * Logging level
     * @var int
     */
    public $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    public $fileName = '/var/log/affirm.log';
}