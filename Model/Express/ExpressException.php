<?php
namespace Astound\Affirm\Model\Express;

/**
 * Error raised while serving the Express Checkout Shipping & Totals callback.
 * Carries the Affirm error_code, and the HTTP status the controller should answer with.
 */
class ExpressException extends \Exception
{
    const UNSUPPORTED_SHIPPING_ZONE = 'UNSUPPORTED_SHIPPING_ZONE';
    const INVALID_SHIPPING_ADDRESS = 'INVALID_SHIPPING_ADDRESS';
    const ORDER_NOT_FOUND = 'ORDER_NOT_FOUND';
    const CURRENCY_MISMATCH = 'CURRENCY_MISMATCH';
    const INVENTORY_UNAVAILABLE = 'INVENTORY_UNAVAILABLE';
    const SHIPPING_METHOD_UNAVAILABLE = 'SHIPPING_METHOD_UNAVAILABLE';
    const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';

    /** @var string */
    private $errorCode;

    /** @var string[] */
    private $fields;

    /** @var int */
    private $httpStatus;

    /**
     * @param string $errorCode
     * @param string $message
     * @param string[] $fields
     * @param int $httpStatus
     */
    public function __construct($errorCode, $message, array $fields = [], $httpStatus = 422)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->fields = $fields;
        $this->httpStatus = $httpStatus;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return int
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Body in the shape Affirm expects: { "errors": [ { error_code, message, fields? } ] }
     *
     * @return array
     */
    public function toResponseBody()
    {
        $error = ['error_code' => $this->errorCode, 'message' => $this->getMessage()];
        if ($this->fields) {
            $error['fields'] = $this->fields;
        }
        return ['errors' => [$error]];
    }
}
