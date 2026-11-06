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

    public function testExpressEnabledAddsVariantAndCallbackUrl()
    {
        $config = $this->build(true, 'https://shop.example.com/affirm/shipping');
        $this->assertSame('express', $config['merchant']['checkout_variant']);
        $this->assertSame(
            'https://shop.example.com/affirm/shipping',
            $config['merchant']['shipping_and_totals_callback_url']
        );
        $this->assertTrue($config['expressCheckout']);
    }

    public function testExpressDisabledLeavesMerchantUnchanged()
    {
        $config = $this->build(false);
        $this->assertSame(
            ['user_confirmation_url', 'user_cancel_url', 'user_confirmation_url_action'],
            array_keys($config['merchant'])
        );
        $this->assertFalse($config['expressCheckout']);
    }
}
