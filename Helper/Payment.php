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

namespace Astound\Affirm\Helper;

use \Magento\Checkout\Model\Session;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Theme\Model\View\Design;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Payment helper
 * The class is responsible
 * For get data from Gateway API
 * Facade class
 *
 * @package Astound\Affirm\Helper
 */
class Payment
{
    /**
     * Affirm payment facade
     *
     * @var \Magento\Payment\Model\Method\Adapter
     */
    protected $payment;

    /**
     * Current checkout quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Method specification factory
     *
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * Customer session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $customerSession;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Design object
     *
     * @var Design
     */
    protected $design;

    /**
     * Media config instance
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $config;

    /**
     * Product image helper instance
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Scope config instance
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Affirm payment helper initialization.
     *
     * @param \Magento\Payment\Model\Method\Adapter              $payment
     * @param Session                                            $session
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Catalog\Model\Product\Media\Config        $config
     * @param StoreManagerInterface                              $storeManagerInterface
     * @param Design                                             $design
     * @param ImageHelper                                        $imageHelper
     * @param ScopeConfigInterface                               $scopeConfigInterface
     */
    public function __construct(
        \Magento\Payment\Model\Method\Adapter $payment,
        Session $session,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product\Media\Config $config,
        StoreManagerInterface $storeManagerInterface,
        Design $design,
        ImageHelper $imageHelper,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->payment = $payment;
        $this->quote = $session->getQuote();
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManagerInterface;
        $this->design = $design;
        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->scopeConfig = $scopeConfigInterface;
    }

    /**
     * Get placeholder image
     *
     * @return string
     */
    public function getPlaceholderImage()
    {
        $this->storeManager->setCurrentStore($this->storeManager->getDefaultStoreView()->getId());
        $this->design->setArea('frontend')->setDefaultDesignTheme();
        $configPlaceholder = $this->scopeConfig->getValue(
            'catalog/placeholder/image_placeholder',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($configPlaceholder) {
            $configPlaceholder = '/placeholder/' . $configPlaceholder;
            return $this->config->getMediaUrl($configPlaceholder);
        }
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * Get payment method availability
     *
     * @return bool|mixed
     */
    public function isAffirmAvailable()
    {
        $checkData = [
            AbstractMethod::CHECK_USE_FOR_CURRENCY,
            AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ];

        $check = $this->methodSpecificationFactory
            ->create($checkData)
            ->isApplicable(
                $this->payment,
                $this->quote
            );
        if ($check) {
            return $this->payment->isAvailable($this->quote);
        }
        return false;
    }
}
