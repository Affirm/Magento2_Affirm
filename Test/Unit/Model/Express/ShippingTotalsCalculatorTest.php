<?php
namespace Astound\Affirm\Test\Unit\Model\Express;

use Astound\Affirm\Model\Express\ExpressException;
use Astound\Affirm\Model\Express\ShippingTotalsCalculator;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class ShippingTotalsCalculatorTest extends TestCase
{
    private function calculator(?string $countryId = 'US'): ShippingTotalsCalculator
    {
        $country = $this->getMockBuilder(Country::class)->disableOriginalConstructor()
            ->onlyMethods(['loadByCode'])->addMethods(['getCountryId'])->getMock();
        $country->method('loadByCode')->willReturnSelf();
        $country->method('getCountryId')->willReturn($countryId);
        $countryFactory = $this->createMock(CountryFactory::class);
        $countryFactory->method('create')->willReturn($country);

        $region = $this->getMockBuilder(Region::class)->disableOriginalConstructor()
            ->onlyMethods(['loadByCode', 'loadByName', 'getId', 'getName'])->getMock();
        $region->method('loadByCode')->willReturnSelf();
        $region->method('loadByName')->willReturnSelf();
        $region->method('getId')->willReturn(12);
        $region->method('getName')->willReturn('California');
        $regionFactory = $this->createMock(RegionFactory::class);
        $regionFactory->method('create')->willReturn($region);

        return new ShippingTotalsCalculator($countryFactory, $regionFactory);
    }

    private function quote(string $allowedCountries = 'US,CA'): Quote
    {
        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturn($allowedCountries);
        $address = $this->getMockBuilder(Address::class)->disableOriginalConstructor()
            ->onlyMethods(['addData', 'collectShippingRates', 'getGroupedAllShippingRates'])->getMock();
        $address->method('addData')->willReturnSelf();
        $address->method('getGroupedAllShippingRates')->willReturn([]);
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->onlyMethods(['getShippingAddress', 'getStore'])->getMock();
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
