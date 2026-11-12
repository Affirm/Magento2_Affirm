<?php
namespace Astound\Affirm\Test\Unit\Model\Ui;

use Astound\Affirm\Model\Config as ConfigAffirm;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private function build(bool $expressActive, string $url = ''): array
    {
        $affirmConfig = $this->createMock(ConfigAffirm::class);
        $affirmConfig->method('isExpressCheckoutActive')->willReturn($expressActive);
        $affirmConfig->method('getExpressShippingUrl')->willReturn($url);
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getUrl')->willReturnArgument(0);

        $provider = new ConfigProvider(
            $this->createMock(ConfigInterface::class),
            $urlBuilder,
            $this->createMock(CheckoutSession::class),
            $this->createMock(ProductMetadataInterface::class),
            $affirmConfig
        );
        return $provider->getConfig()['payment'][ConfigProvider::CODE];
    }

    private const STANDARD_KEYS = ['user_confirmation_url', 'user_cancel_url', 'user_confirmation_url_action'];

    public function testExpressEnabledBuildsSeparateExpressMerchant()
    {
        $config = $this->build(true, 'https://shop.example.com/affirm/shipping');
        $this->assertSame('express', $config['expressMerchant']['checkout_variant']);
        $this->assertSame(
            'https://shop.example.com/affirm/shipping',
            $config['expressMerchant']['shipping_and_totals_callback_url']
        );
        $this->assertTrue($config['expressCheckout']);
    }

    public function testStandardMerchantUnchangedWhenExpressEnabled()
    {
        $config = $this->build(true, 'https://shop.example.com/affirm/shipping');
        $this->assertSame(self::STANDARD_KEYS, array_keys($config['merchant']));
    }

    public function testExpressDisabledHasNoExpressMerchant()
    {
        $config = $this->build(false);
        $this->assertSame(self::STANDARD_KEYS, array_keys($config['merchant']));
        $this->assertNull($config['expressMerchant']);
        $this->assertFalse($config['expressCheckout']);
    }
}
