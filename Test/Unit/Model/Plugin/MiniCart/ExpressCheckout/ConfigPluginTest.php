<?php
namespace Astound\Affirm\Test\Unit\Model\Plugin\MiniCart\ExpressCheckout;

use Astound\Affirm\Model\Config;
use Astound\Affirm\Model\Plugin\MiniCart\ExpressCheckout\ConfigPlugin;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Checkout\Block\Cart\Sidebar;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class ConfigPluginTest extends TestCase
{
    private function run(bool $active, array $result): array
    {
        $affirmConfig = $this->createMock(Config::class);
        $affirmConfig->method('isExpressCheckoutActive')->willReturn($active);
        $provider = $this->createMock(ConfigProvider::class);
        $provider->method('getConfig')->willReturn(['payment' => [ConfigProvider::CODE => [
            'apiKeyPublic' => 'pk',
            'script' => 'https://cdn1.affirm.com/js/v2/affirm.js',
            'locale' => 'en_US',
            'countryCode' => 'USA',
            'expressMerchant' => ['checkout_variant' => 'express']
        ]]]);
        $store = $this->createMock(StoreInterface::class);
        $store->method('getCode')->willReturn('default');
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $plugin = new ConfigPlugin($affirmConfig, $provider, $storeManager);
        return $plugin->afterGetConfig($this->createMock(Sidebar::class), $result);
    }

    public function testAddsExpressConfigWhenActive()
    {
        $result = $this->run(true, ['checkoutUrl' => '/checkout']);
        $this->assertSame('/checkout', $result['checkoutUrl']);
        $this->assertSame('default', $result['affirmExpress']['storeCode']);
        $this->assertSame('pk', $result['affirmExpress']['publicApiKey']);
        $this->assertSame('express', $result['affirmExpress']['merchant']['checkout_variant']);
    }

    public function testLeavesConfigUnchangedWhenInactive()
    {
        $this->assertSame(['checkoutUrl' => '/checkout'], $this->run(false, ['checkoutUrl' => '/checkout']));
    }
}
