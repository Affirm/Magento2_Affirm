<?php

namespace Astound\Affirm\Model\Plugin\MiniCart\ExpressCheckout;

use Astound\Affirm\Model\Config;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Exposes what the minicart Express Checkout button needs as window.checkout.affirmExpress.
 * The minicart is rendered outside the checkout page, so window.checkoutConfig is not available there.
 * Nothing is added unless Express Checkout is active, so the standard minicart config is unchanged.
 */
class ConfigPlugin
{
    /** @var Config */
    private $affirmConfig;

    /** @var ConfigProvider */
    private $configProvider;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        Config $affirmConfig,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->affirmConfig = $affirmConfig;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, array $result)
    {
        if (!$this->affirmConfig->isExpressCheckoutMinicartActive()) {
            return $result;
        }
        $gateway = $this->configProvider->getConfig()['payment'][ConfigProvider::CODE];
        $result['affirmExpress'] = [
            'storeCode' => $this->storeManager->getStore()->getCode(),
            'publicApiKey' => $gateway['apiKeyPublic'],
            'script' => $gateway['script'],
            'locale' => $gateway['locale'],
            'countryCode' => $gateway['countryCode'],
            'merchant' => $gateway['expressMerchant']
        ];
        return $result;
    }
}
