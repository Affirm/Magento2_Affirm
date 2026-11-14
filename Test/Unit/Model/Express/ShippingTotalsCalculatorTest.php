<?php
namespace Astound\Affirm\Test\Unit\Model\Express;

use Astound\Affirm\Model\Express\ExpressException;
use Astound\Affirm\Model\Express\ShippingTotalsCalculator;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\DataObject;
use Magento\Directory\Model\RegionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class ShippingTotalsCalculatorTest extends TestCase
{
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

    private function quote(string $allowedCountries = 'US,CA'): Quote
    {
        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturn($allowedCountries);
        $address = $this->createMock(Address::class);
        $address->method('getGroupedAllShippingRates')->willReturn([]);
        $quote = $this->createMock(Quote::class);
        $quote->method('getShippingAddress')->willReturn($address);
        $quote->method('getStore')->willReturn($store);
        return $quote;
    }

    private function shipping(): array
    {
        return ['line1' => '1 Main', 'city' => 'SF', 'state' => 'CA', 'zipcode' => '94105', 'country' => 'USA'];
    }

    public function testMinorUnitConversion()
    {
        $calc = $this->calculator();
        $this->assertSame(1099, $calc->toMinorUnits(10.99, 'USD'));
        $this->assertSame(1999, $calc->toMinorUnits('19.99', 'usd'));
        $this->assertSame(0, $calc->toMinorUnits(null, 'USD'));
        $this->assertSame(1500, $calc->toMinorUnits(1500, 'JPY'));
    }

    public function testNoRatesThrowsShippingMethodUnavailable()
    {
        try {
            $this->calculator()->calculate($this->quote(), $this->shipping(), 'USD');
            $this->fail('Expected exception');
        } catch (ExpressException $e) {
            $this->assertSame(ExpressException::SHIPPING_METHOD_UNAVAILABLE, $e->getErrorCode());
            $this->assertSame(422, $e->getHttpStatus());
        }
    }

    public function testUnknownCountryThrowsInvalidAddress()
    {
        try {
            $this->calculator(null)->calculate($this->quote(), $this->shipping(), 'USD');
            $this->fail('Expected exception');
        } catch (ExpressException $e) {
            $this->assertSame(ExpressException::INVALID_SHIPPING_ADDRESS, $e->getErrorCode());
            $this->assertSame(['shipping.country'], $e->getFields());
        }
    }

    public function testDisallowedCountryThrowsUnsupportedZone()
    {
        try {
            $this->calculator('MX')->calculate($this->quote(), $this->shipping(), 'USD');
            $this->fail('Expected exception');
        } catch (ExpressException $e) {
            $this->assertSame(ExpressException::UNSUPPORTED_SHIPPING_ZONE, $e->getErrorCode());
        }
    }

    public function testExceptionBodyShape()
    {
        $e = new ExpressException(ExpressException::ORDER_NOT_FOUND, 'nope', ['order_id']);
        $this->assertSame(
            ['errors' => [['error_code' => 'ORDER_NOT_FOUND', 'message' => 'nope', 'fields' => ['order_id']]]],
            $e->toResponseBody()
        );
    }
}
