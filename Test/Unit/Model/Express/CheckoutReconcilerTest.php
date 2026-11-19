<?php
namespace Astound\Affirm\Test\Unit\Model\Express;

use Astound\Affirm\Gateway\Http\Client\ClientService;
use Astound\Affirm\Model\Express\CheckoutReconciler;
use Astound\Affirm\Model\Express\ShippingTotalsCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\TestCase;

class CheckoutReconcilerTest extends TestCase
{
    private const SHIPPING = [
        'name' => ['first' => 'Jane', 'last' => 'Doe'],
        'address' => ['line1' => '633 Folsom St', 'city' => 'San Francisco', 'state' => 'CA', 'zipcode' => '94107', 'country' => 'US'],
    ];

    private function reconciler($response, ?ShippingTotalsCalculator $calculator = null, bool $clientThrows = false): CheckoutReconciler
    {
        $factory = $this->createMock(TransferFactoryInterface::class);
        $factory->method('create')->willReturn($this->createMock(TransferInterface::class));
        $client = $this->createMock(ClientService::class);
        if ($clientThrows) {
            $client->method('placeRequest')->willThrowException(new \Exception('boom'));
        } else {
            $client->method('placeRequest')->willReturn($response);
        }
        return new CheckoutReconciler(
            $factory,
            $client,
            $calculator ?: $this->createMock(ShippingTotalsCalculator::class)
        );
    }

    private function quote(bool $rateExists = true, bool $expectCollect = true): array
    {
        $shipping = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingRateByCode', 'collectShippingRates', 'addData', 'setData'])
            ->getMock();
        $shipping->method('getShippingRateByCode')->willReturn($rateExists ? new \stdClass() : false);
        $billing = $this->createMock(Address::class);
        // setCustomerEmail is a magic (__call) setter, so it must be declared with addMethods()
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIsVirtual', 'getShippingAddress', 'getBillingAddress', 'collectTotals'])
            ->addMethods(['setCustomerEmail'])
            ->getMock();
        $quote->method('getIsVirtual')->willReturn(false);
        $quote->method('getShippingAddress')->willReturn($shipping);
        $quote->method('getBillingAddress')->willReturn($billing);
        $quote->expects($expectCollect ? $this->once() : $this->never())->method('collectTotals');
        return [$quote, $shipping, $billing];
    }

    public function testStandardCheckoutIsLeftUntouched(): void
    {
        [$quote] = $this->quote(true, false);
        $calculator = $this->createMock(ShippingTotalsCalculator::class);
        $calculator->expects($this->never())->method('applyAddress');

        $result = $this->reconciler(['checkout_id' => 'abc', 'shipping' => self::SHIPPING], $calculator)
            ->reconcile($quote, 'tok', 'USA');

        $this->assertFalse($result);
    }

    public function testExpressCheckoutAppliesAddressShippingMethodAndRecollectsTotals(): void
    {
        [$quote, $shipping, $billing] = $this->quote();
        $calculator = $this->createMock(ShippingTotalsCalculator::class);
        $calculator->expects($this->once())->method('applyAddress')->with($quote, $this->callback(
            fn($a) => $a['line1'] === '633 Folsom St' && $a['zipcode'] === '94107' && $a['country'] === 'US'
        ));
        $shipping->expects($this->once())->method('setData')->with('shipping_method', 'flatrate_flatrate');
        $billing->expects($this->once())->method('addData')->with($this->callback(
            fn($d) => $d['city'] === 'San Francisco' && $d['email'] === 'jane@example.com'
        ));
        $quote->expects($this->once())->method('setCustomerEmail')->with('jane@example.com');

        $result = $this->reconciler([
            'checkout_id' => 'abc',
            'checkout_variant' => 'express',
            'shipping' => self::SHIPPING,
            'billing' => ['email' => 'jane@example.com'] + self::SHIPPING,
            'shipping_option' => ['shipping_type' => 'flatrate_flatrate'],
        ], $calculator)->reconcile($quote, 'tok', 'USA');

        $this->assertTrue($result);
    }

    public function testReadCheckoutFailureThrows(): void
    {
        [$quote] = $this->quote(true, false);
        $this->expectException(LocalizedException::class);
        $this->reconciler([], null, true)->reconcile($quote, 'tok', 'USA');
    }

    public function testEmptyReadCheckoutResponseThrows(): void
    {
        [$quote] = $this->quote(true, false);
        $this->expectException(LocalizedException::class);
        $this->reconciler(['status_code' => 400])->reconcile($quote, 'tok', 'USA');
    }

    public function testUnavailableShippingMethodThrowsWithoutCollectingTotals(): void
    {
        [$quote] = $this->quote(false, false);
        $this->expectException(LocalizedException::class);
        $this->reconciler([
            'checkout_id' => 'abc',
            'checkout_variant' => 'express',
            'shipping' => self::SHIPPING,
            'shipping_option' => ['shipping_type' => 'gone_gone'],
        ])->reconcile($quote, 'tok', 'USA');
    }
}
