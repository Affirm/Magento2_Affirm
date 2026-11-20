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
        $quote = $this->createMock(Quote::class);
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

        $result = $this->reconciler(['checkout_status' => 'confirmed', 'shipping' => self::SHIPPING], $calculator)
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
        // setCollectShippingRates() is a magic setter that also lands in setData(), so record every call
        $setData = [];
        $shipping->method('setData')->willReturnCallback(
            function ($key, $value = null) use (&$setData, $shipping) {
                $setData[$key] = $value;
                return $shipping;
            }
        );
        $billing->expects($this->once())->method('addData')->with($this->callback(
            fn($d) => $d['city'] === 'San Francisco' && $d['email'] === 'jane@example.com'
        ));
        $quote->expects($this->once())->method('setData')->with('customer_email', 'jane@example.com');

        $result = $this->reconciler([
            'checkout_status' => 'confirmed',
            'merchant' => ['checkout_variant' => 'express'],
            'shipping' => ['shipping_type' => 'flatrate_flatrate'] + self::SHIPPING,
            'billing' => ['email' => 'jane@example.com'] + self::SHIPPING,
        ], $calculator)->reconcile($quote, 'tok', 'USA');

        $this->assertTrue($result);
        $this->assertSame('flatrate_flatrate', $setData['shipping_method'] ?? null);
    }

    public function testExpressResponseShapeFromAffirmWithoutBillingAddressOrShippingName(): void
    {
        [$quote, $shipping, $billing] = $this->quote();
        $calculator = $this->createMock(ShippingTotalsCalculator::class);
        $setData = [];
        $shipping->method('setData')->willReturnCallback(
            function ($key, $value = null) use (&$setData, $shipping) {
                $setData[$key] = $value;
                return $shipping;
            }
        );
        $billing->expects($this->once())->method('addData')->with($this->callback(
            fn($d) => $d['firstname'] === 'Test' && $d['lastname'] === 'Profile'
                && $d['city'] === 'San Francisco' && $d['telephone'] === '+1-317-522-4242'
        ));

        $result = $this->reconciler([
            'checkout_status' => 'confirmed',
            'merchant' => ['checkout_variant' => 'express'],
            'shipping' => ['name' => [], 'shipping_type' => 'two-day'] + self::SHIPPING,
            'billing' => [
                'name' => ['full' => 'Test Profile', 'first' => 'Test', 'last' => 'Profile'],
                'email' => 'jane@example.com',
                'phone_number' => '+1-317-522-4242',
            ],
        ], $calculator)->reconcile($quote, 'tok', 'USA');

        $this->assertTrue($result);
        // Affirm's own option (two-day) is ignored; flat rate is used for now
        $this->assertSame('flatrate_flatrate', $setData['shipping_method'] ?? null);
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
            'checkout_status' => 'confirmed',
            'merchant' => ['checkout_variant' => 'express'],
            'shipping' => ['shipping_type' => 'gone_gone'] + self::SHIPPING,
        ])->reconcile($quote, 'tok', 'USA');
    }
}
