<?php
namespace Astound\Affirm\Test\Unit\Controller\Express;

use Astound\Affirm\Controller\Express\ShippingTotals;
use Astound\Affirm\Logger\Logger;
use Astound\Affirm\Model\Config;
use Astound\Affirm\Model\Express\ExpressException;
use Astound\Affirm\Model\Express\QuoteResolver;
use Astound\Affirm\Model\Express\ShippingTotalsCalculator;
use Astound\Affirm\Model\Express\SignatureValidator;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

/**
 * Drives the whole Shipping & Totals call the way Affirm makes it: HTTP headers and raw body in, real signature
 * validation, payload validation and shipping calculation, status code and JSON body out. Only Magento's
 * persistence (quote lookup) and request/response plumbing are replaced.
 */
class ShippingTotalsTest extends TestCase
{
    private const KEY = 'private-key';

    /** @var int|null */
    private $status;

    /** @var array|null */
    private $body;

    private function payload(array $override = []): array
    {
        return array_replace_recursive([
            'order_id' => '000000017',
            'currency' => 'USD',
            'shipping' => [
                'line1' => '650 California St',
                'line2' => 'Floor 12',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zipcode' => '94108',
                'country' => 'USA',
            ],
        ], $override);
    }

    private function signature(string $body, ?int $timestamp = null, string $key = self::KEY): string
    {
        $timestamp = $timestamp ?? time();
        return 't=' . $timestamp . ',v1=' . hash_hmac('sha512', $timestamp . '.' . $body, $key);
    }

    private function rate(): DataObject
    {
        return new DataObject([
            'carrier' => 'flatrate',
            'method' => 'flatrate',
            'carrier_title' => 'Flat Rate',
            'method_title' => 'Fixed',
        ]);
    }

    private function quote(array $rates, array $items = [], string $allowedCountries = 'US,CA'): DataObject
    {
        $store = new class($allowedCountries) extends DataObject {
            private $allowed;

            public function __construct($allowed)
            {
                parent::__construct();
                $this->allowed = $allowed;
            }

            public function getConfig($path)
            {
                return $this->allowed;
            }
        };

        $address = new class($rates) extends DataObject {
            private $rates;

            public function __construct($rates)
            {
                parent::__construct(['shipping_amount' => 5.0, 'tax_amount' => 1.64]);
                $this->rates = $rates;
            }

            public function collectShippingRates()
            {
                return $this;
            }

            public function getGroupedAllShippingRates()
            {
                return $this->rates;
            }
        };

        return new class($address, $store, $items) extends DataObject {
            private $address;
            private $store;
            private $items;

            public function __construct($address, $store, $items)
            {
                parent::__construct();
                $this->address = $address;
                $this->store = $store;
                $this->items = $items;
            }

            public function getShippingAddress()
            {
                return $this->address;
            }

            public function getStore()
            {
                return $this->store;
            }

            public function getAllItems()
            {
                return $this->items;
            }

            public function collectTotals()
            {
                return $this;
            }

            public function getGrandTotal()
            {
                return 28.64;
            }

            public function getSubtotal()
            {
                return 22.0;
            }
        };
    }

    private function calculator(?string $countryId = 'US'): ShippingTotalsCalculator
    {
        $country = new class($countryId) extends DataObject {
            public function __construct($id)
            {
                parent::__construct(['country_id' => $id]);
            }

            public function loadByCode($code)
            {
                return $this;
            }
        };
        $countryFactory = $this->createMock(CountryFactory::class);
        $countryFactory->method('create')->willReturn($country);

        $region = new class extends DataObject {
            public function __construct()
            {
                parent::__construct(['id' => 12, 'name' => 'California']);
            }

            public function loadByCode($code, $countryId)
            {
                return $this;
            }

            public function loadByName($name, $countryId)
            {
                return $this;
            }
        };
        $regionFactory = $this->createMock(RegionFactory::class);
        $regionFactory->method('create')->willReturn($region);

        return new ShippingTotalsCalculator($countryFactory, $regionFactory);
    }

    /**
     * Make the call and return [status, body].
     *
     * @param string|null $rawBody raw request body (defaults to the encoded default payload)
     * @param array $headers request headers, defaults to a valid JSON content type and signature
     * @param \Closure|null $configure receives the mocks to adjust: (Config, QuoteResolver, Logger)
     */
    private function call(
        ?string $rawBody = null,
        ?array $headers = null,
        ?DataObject $quote = null,
        bool $expressEnabled = true,
        ?string $countryId = 'US',
        ?\Closure $configure = null
    ): array {
        $rawBody = $rawBody ?? json_encode($this->payload());
        $headers = $headers ?? [
            'Content-Type' => 'application/json',
            SignatureValidator::HEADER => $this->signature($rawBody),
        ];

        $request = $this->createMock(Http::class);
        $request->method('getHeader')->willReturnCallback(fn($name) => $headers[$name] ?? false);
        $request->method('getContent')->willReturn($rawBody);

        $this->status = null;
        $this->body = null;
        $json = $this->createMock(Json::class);
        $json->method('setHttpResponseCode')->willReturnCallback(function ($code) use ($json) {
            $this->status = $code;
            return $json;
        });
        $json->method('setData')->willReturnCallback(function ($data) use ($json) {
            $this->body = $data;
            return $json;
        });
        $jsonFactory = $this->createMock(JsonFactory::class);
        $jsonFactory->method('create')->willReturn($json);

        $config = $this->createMock(Config::class);
        $config->method('isExpressCheckoutEnabled')->willReturn($expressEnabled);
        $config->method('getPrivateApiKey')->willReturn(self::KEY);

        $resolver = $this->createMock(QuoteResolver::class);
        $resolver->method('resolve')->willReturn($quote ?? $this->quote([[$this->rate()]]));
        $logger = $this->createMock(Logger::class);
        if ($configure) {
            $configure($config, $resolver, $logger);
        }

        $controller = new ShippingTotals(
            $request,
            $jsonFactory,
            $config,
            new SignatureValidator(),
            $resolver,
            $this->calculator($countryId),
            $logger
        );
        $controller->execute();

        return [$this->status, $this->body];
    }

    private function assertError(string $code, ?array $fields = null): void
    {
        $this->assertSame(422, $this->status);
        $this->assertCount(1, $this->body['errors']);
        $error = $this->body['errors'][0];
        $this->assertSame($code, $error['error_code']);
        $this->assertNotEmpty($error['message']);
        if ($fields !== null) {
            $this->assertSame($fields, $error['fields'] ?? null);
        }
    }

    public function testValidCallReturnsShippingOptionsAndTotals(): void
    {
        $this->call();

        $this->assertSame(200, $this->status);
        $this->assertSame([
            'order_id' => '000000017',
            'currency' => 'USD',
            'subtotal' => 2200,
            'shipping_options' => [[
                'shipping_type' => 'flatrate_flatrate',
                'shipping_label' => 'Flat Rate - Fixed',
                'shipping_amount' => 500,
                'tax_amount' => 164,
                'total' => 2864,
            ]],
        ], $this->body);
    }

    public function testCurrencyIsReturnedUppercase(): void
    {
        $this->call(json_encode($this->payload(['currency' => 'usd'])));

        $this->assertSame(200, $this->status);
        $this->assertSame('USD', $this->body['currency']);
    }

    public function testMultipleRatesReturnOneOptionEach(): void
    {
        $second = new DataObject([
            'carrier' => 'tablerate',
            'method' => 'bestway',
            'carrier_title' => 'Best Way',
            'method_title' => 'Table Rate',
        ]);
        $this->call(null, null, $this->quote([[$this->rate()], [$second]]));

        $this->assertSame(200, $this->status);
        $this->assertSame(
            ['flatrate_flatrate', 'tablerate_bestway'],
            array_column($this->body['shipping_options'], 'shipping_type')
        );
    }

    public function testRatesWithErrorsAreSkipped(): void
    {
        $broken = new DataObject(['carrier' => 'ups', 'method' => '2day', 'error_message' => 'down']);
        $this->call(null, null, $this->quote([[$broken, $this->rate()]]));

        $this->assertSame(200, $this->status);
        $this->assertSame(['flatrate_flatrate'], array_column($this->body['shipping_options'], 'shipping_type'));
    }

    public function testDisabledExpressCheckoutReturns404(): void
    {
        $this->call(null, null, null, false);

        $this->assertSame(404, $this->status);
        $this->assertSame([], $this->body);
    }

    public function testNonJsonContentTypeReturns415(): void
    {
        $body = json_encode($this->payload());
        $this->call($body, ['Content-Type' => 'text/plain', SignatureValidator::HEADER => $this->signature($body)]);

        $this->assertSame(415, $this->status);
        $this->assertSame([], $this->body);
    }

    public function testMissingSignatureReturns401WithEmptyBody(): void
    {
        $this->call(null, ['Content-Type' => 'application/json']);

        $this->assertSame(401, $this->status);
        $this->assertSame([], $this->body);
    }

    public function testSignatureFromAnotherKeyReturns401(): void
    {
        $body = json_encode($this->payload());
        $this->call($body, [
            'Content-Type' => 'application/json',
            SignatureValidator::HEADER => $this->signature($body, null, 'some-other-key'),
        ]);

        $this->assertSame(401, $this->status);
        $this->assertSame([], $this->body);
    }

    public function testTamperedBodyReturns401(): void
    {
        $signed = json_encode($this->payload());
        $tampered = json_encode($this->payload(['shipping' => ['zipcode' => '00000']]));
        $this->call($tampered, [
            'Content-Type' => 'application/json',
            SignatureValidator::HEADER => $this->signature($signed),
        ]);

        $this->assertSame(401, $this->status);
    }

    public function testStaleTimestampReturns401(): void
    {
        $body = json_encode($this->payload());
        $this->call($body, [
            'Content-Type' => 'application/json',
            SignatureValidator::HEADER => $this->signature($body, time() - 3600),
        ]);

        $this->assertSame(401, $this->status);
    }

    public function testMalformedJsonBodyReturnsInvalidShippingAddress(): void
    {
        $this->call('not json');

        $this->assertError(ExpressException::INVALID_SHIPPING_ADDRESS);
    }

    public function testMissingOrderIdReturnsOrderNotFound(): void
    {
        $payload = $this->payload();
        unset($payload['order_id']);
        $this->call(json_encode($payload));

        $this->assertError(ExpressException::ORDER_NOT_FOUND, ['order_id']);
    }

    public function testMissingCurrencyReturnsCurrencyMismatch(): void
    {
        $payload = $this->payload();
        unset($payload['currency']);
        $this->call(json_encode($payload));

        $this->assertError(ExpressException::CURRENCY_MISMATCH, ['currency']);
    }

    public function testMissingShippingFieldsAreListed(): void
    {
        $payload = $this->payload();
        unset($payload['shipping']['zipcode'], $payload['shipping']['city']);
        $this->call(json_encode($payload));

        $this->assertError(ExpressException::INVALID_SHIPPING_ADDRESS, ['shipping.city', 'shipping.zipcode']);
    }

    public function testMissingShippingObjectReturnsInvalidShippingAddress(): void
    {
        $payload = $this->payload();
        unset($payload['shipping']);
        $this->call(json_encode($payload));

        $this->assertError(ExpressException::INVALID_SHIPPING_ADDRESS);
    }

    public function testUnknownOrderReturnsOrderNotFound(): void
    {
        $this->call(null, null, null, true, 'US', function ($config, $resolver) {
            $resolver->method('resolve')->willThrowException(
                new ExpressException(ExpressException::ORDER_NOT_FOUND, 'No active order', ['order_id'])
            );
        });

        $this->assertError(ExpressException::ORDER_NOT_FOUND, ['order_id']);
    }

    public function testNoShippingRatesReturnsShippingMethodUnavailable(): void
    {
        $this->call(null, null, $this->quote([]));

        $this->assertError(ExpressException::SHIPPING_METHOD_UNAVAILABLE);
    }

    public function testUnknownCountryReturnsInvalidShippingAddress(): void
    {
        $this->call(null, null, null, true, null);

        $this->assertError(ExpressException::INVALID_SHIPPING_ADDRESS, ['shipping.country']);
    }

    public function testCountryTheStoreDoesNotShipToReturnsUnsupportedZone(): void
    {
        $this->call(null, null, $this->quote([[$this->rate()]], [], 'CA'));

        $this->assertError(ExpressException::UNSUPPORTED_SHIPPING_ZONE, ['shipping.country']);
    }

    public function testOutOfStockItemReturnsInventoryUnavailable(): void
    {
        $item = new DataObject(['has_error' => true]);
        $this->call(null, null, $this->quote([[$this->rate()]], [$item]));

        $this->assertError(ExpressException::INVENTORY_UNAVAILABLE);
    }

    public function testUnexpectedFailureReturnsInternalServerErrorAndLogsIt(): void
    {
        $this->call(null, null, null, true, 'US', function ($config, $resolver, $logger) {
            $resolver->method('resolve')->willThrowException(new \RuntimeException('db is down'));
            $logger->expects($this->once())->method('error')->with($this->stringContains('db is down'));
        });

        $this->assertError(ExpressException::INTERNAL_SERVER_ERROR);
        $this->assertSame('Unexpected error', $this->body['errors'][0]['message']);
    }
}
