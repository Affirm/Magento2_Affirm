<?php
namespace Astound\Affirm\Controller\Express;

use Astound\Affirm\Logger\Logger;
use Astound\Affirm\Model\Config;
use Astound\Affirm\Model\Express\ExpressException;
use Astound\Affirm\Model\Express\QuoteResolver;
use Astound\Affirm\Model\Express\ShippingTotalsCalculator;
use Astound\Affirm\Model\Express\SignatureValidator;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Express Checkout Shipping & Totals endpoint (Affirm -> merchant, server to server).
 * Reachable at /affirm/express/shippingtotals; the only authentication is the Affirm signature.
 */
class ShippingTotals implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /** @var Http */
    private $request;

    /** @var JsonFactory */
    private $jsonFactory;

    /** @var Config */
    private $config;

    /** @var SignatureValidator */
    private $signatureValidator;

    /** @var QuoteResolver */
    private $quoteResolver;

    /** @var ShippingTotalsCalculator */
    private $calculator;

    /** @var Logger */
    private $logger;

    public function __construct(
        Http $request,
        JsonFactory $jsonFactory,
        Config $config,
        SignatureValidator $signatureValidator,
        QuoteResolver $quoteResolver,
        ShippingTotalsCalculator $calculator,
        Logger $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
        $this->signatureValidator = $signatureValidator;
        $this->quoteResolver = $quoteResolver;
        $this->calculator = $calculator;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result =$this->jsonFactory->create();
        try {
            if (!$this->config->isExpressCheckoutEnabled()) {
                return $result->setHttpResponseCode(404)->setData([]);
            }
            if (stripos((string)$this->request->getHeader('Content-Type'), 'application/json') !== 0) {
                return $result->setHttpResponseCode(415)->setData([]);
            }

            $rawBody = (string)$this->request->getContent();
            $this->signatureValidator->validate(
                (string)$this->request->getHeader(SignatureValidator::HEADER),
                $rawBody,
                (string)$this->config->getPrivateApiKey()
            );

            $payload = json_decode($rawBody, true);
            $this->validatePayload($payload);

            $quote = $this->quoteResolver->resolve($payload['order_id'], $payload['currency']);
            $options = $this->calculator->calculate($quote, $payload['shipping'], $payload['currency']);

            return $result->setHttpResponseCode(200)->setData([
                'order_id' => $payload['order_id'],
                'currency' => strtoupper($payload['currency']),
                'subtotal' => $this->calculator->getSubtotal($quote, $payload['currency']),
                'shipping_options' => $options,
            ]);
        } catch (ExpressException $e) {
            $this->logger->warning('Affirm express shipping-totals: ' . $e->getErrorCode());
            if ($e->getHttpStatus() === 401) {
                return $result->setHttpResponseCode(401)->setData([]);
            }
            return $result->setHttpResponseCode($e->getHttpStatus())->setData($e->toResponseBody());
        } catch (\Throwable $e) {
            $this->logger->error('Affirm express shipping-totals failed: ' . $e->getMessage());
            $error = new ExpressException(ExpressException::INTERNAL_SERVER_ERROR, 'Unexpected error');
            return $result->setHttpResponseCode(422)->setData($error->toResponseBody());
        }
    }

    /**
     * @param mixed $payload
     * @return void
     * @throws ExpressException
     */
    private function validatePayload($payload)
    {
        if (!is_array($payload)) {
            throw new ExpressException(ExpressException::INVALID_SHIPPING_ADDRESS, 'Malformed request body');
        }
        if (empty($payload['order_id']) || !is_string($payload['order_id'])) {
            throw new ExpressException(ExpressException::ORDER_NOT_FOUND, 'order_id is required', ['order_id']);
        }
        if (empty($payload['currency']) || !is_string($payload['currency'])) {
            throw new ExpressException(ExpressException::CURRENCY_MISMATCH, 'currency is required', ['currency']);
        }
        $missing = [];
        foreach (['line1', 'city', 'state', 'zipcode', 'country'] as $field) {
            if (empty($payload['shipping'][$field]) || !is_string($payload['shipping'][$field])) {
                $missing[] = 'shipping.' . $field;
            }
        }
        if (!isset($payload['shipping']) || !is_array($payload['shipping']) || $missing) {
            throw new ExpressException(
                ExpressException::INVALID_SHIPPING_ADDRESS,
                'Shipping address is missing or malformed',
                $missing ?: ['shipping']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Server-to-server POST from Affirm: the signature replaces the form key.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
