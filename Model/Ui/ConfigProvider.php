<?php
/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ProductMetadataInterface;
use Astound\Affirm\Model\Config as ConfigAffirm;

/**
 * Class ConfigProvider
 * Config provider for the payment method
 *
 * @package Astound\Affirm\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**#@+
     * Define constants
     */
    const CODE = 'affirm_gateway';
    const SUCCESS = 0;
    const FRAUD = 1;
    /**#@-*/

    /**
     * Affirm config model
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmConfig;

    /**
     * Injected config object
     *
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $config;

    /**
     * Injected url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Product metadata object
     *
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Inject all needed object for getting data from config
     *
     * @param ConfigInterface          $config
     * @param UrlInterface             $urlInterface
     * @param CheckoutSession          $checkoutSession
     * @param ProductMetadataInterface $productMetadata
     * @param ConfigAffirm             $configAffirm
     */
    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlInterface,
        CheckoutSession $checkoutSession,
        ProductMetadataInterface $productMetadata,
        ConfigAffirm $configAffirm
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlInterface;
        $this->checkoutSession = $checkoutSession;
        $this->productMetadata = $productMetadata;
        $this->affirmConfig = $configAffirm;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        self::SUCCESS => __('Success'),
                        self::FRAUD => __('Fraud')
                    ],
                    'apiKeyPublic' => $this->affirmConfig->getPublicApiKey(),
                    'apiUrl' => $this->affirmConfig->getApiUrl(),
                    'merchant' => [
                        'user_confirmation_url' => $this->urlBuilder
                                ->getUrl('affirm/payment/confirm', ['_secure' => true]),
                        'user_cancel_url' => $this->urlBuilder
                                ->getUrl('affirm/payment/cancel', ['_secure' => true]),
                        'user_confirmation_url_action' => 'POST'
                    ],
                    'config' => [
                        'financial_product_key' => null
                    ],
                    'script' => $this->affirmConfig->getScript(),
                    'countryCode' => $this->affirmConfig->getCountryCode(),
                    'locale' => $this->affirmConfig->getLocale(),
                    'currency' => $this->affirmConfig->getCurrency(),
                    'redirectUrl' => $this->urlBuilder->getUrl('affirm/checkout/start', ['_secure' => true]),
                    'afterAffirmConf' => $this->config->getValue('after_affirm_conf'),
                    'logoSrc' => $this->config->getValue('icon'),
                    'info' => $this->config->getValue('info'),
                    'visibleType' => $this->config->getValue('control') ? true: false,
                    'edu' => $this->affirmConfig->getEdu() ? true : false
                ]
            ]
        ];
    }
}
