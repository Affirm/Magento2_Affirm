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

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Theme\Model\View\Design;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

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
     * Country code for address validation
     */
    const VALIDATE_COUNTRY = 'US';

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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Stock registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Affirm payment helper initialization.
     *
     * @param \Magento\Payment\Model\Method\Adapter                $payment
     * @param Session                                              $session
     * @param \Magento\Payment\Model\Checks\SpecificationFactory   $methodSpecificationFactory
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Catalog\Model\Product\Media\Config          $config
     * @param StoreManagerInterface                                $storeManagerInterface
     * @param Design                                               $design
     * @param ImageHelper                                          $imageHelper
     * @param ScopeConfigInterface                                 $scopeConfigInterface
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
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
        ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
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
        $this->coreRegistry = $registry;
        $this->stockRegistry = $stockRegistry;
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
        if ($this->quote->getIsVirtual() && !$this->quote->getCustomerId()) {
            $checkData[] = AbstractMethod::CHECK_USE_FOR_COUNTRY;
        }

        $check = $this->methodSpecificationFactory
            ->create($checkData)
            ->isApplicable(
                $this->payment,
                $this->quote
            );

        if ($check && $this->validateVirtual()) {
            return $this->payment->isAvailable($this->quote);
        }
        return false;
    }

    /**
     * Get affirm method availability for product page
     *
     * @param Product|null $product
     * @return bool|mixed
     */
    public function isAffirmAvailableForProduct(Product $product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $check = $this->payment->isAvailable();
        if ($check && $this->payment->getConfigData('disable_for_backordered_items') && $product && $product->getId()) {
            if ($product->isComposite()) {
                $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($associatedProducts as $associatedProduct) {
                    $stockItem = $this->stockRegistry->getStockItem($associatedProduct->getId());
                    if ($stockItem->getBackorders() && ($stockItem->getQty() < 1)) {
                        $check = false;
                    }
                }
            } else {
                $stockItem = $this->stockRegistry->getStockItem($product->getId());
                if ($stockItem->getBackorders() && ($stockItem->getQty() < 1)) {
                    $check = false;
                }
            }
        }
        return $check;
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Return catalog current category object
     *
     * @return \Magento\Catalog\Model\Category
     */

    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Check if product is configurable
     *
     * @param Product $product
     * @return bool
     */
    public function isProductConfigurable(Product $product)
    {
        return $product->getTypeId() === ConfigurableProductType::TYPE_CODE;
    }

    /**
     * Get configurable product options
     *
     * @param Product $product
     * @return array
     */
    public function getConfigurableProductBackordersOptions(Product $product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if ($this->payment->getConfigData('disable_for_backordered_items') && $this->isProductConfigurable($product)) {
            /** @var ConfigurableProductType $productTypeConfigurable */
            $productTypeConfigurable = $product->getTypeInstance();
            $childProducts = $productTypeConfigurable->getUsedProducts($product);
            $configurableAttributes = $productTypeConfigurable->getConfigurableAttributesAsArray($product);
            $result = array();
            /** @var Product $childProduct */
            foreach ($childProducts as $childProduct) {
                foreach ($configurableAttributes as $configurableAttribute) {
                    $result[$childProduct->getId()][$configurableAttribute['attribute_id']] =
                        $childProduct[$configurableAttribute['attribute_code']];
                }
                $stockItem = $this->stockRegistry->getStockItem($childProduct->getId());
                $result[$childProduct->getId()]['backorders']
                    = $stockItem->getBackorders() && ($stockItem->getQty() < 1);
            }
            return $result;
        }
        return [];
    }

    /**
     * Validate for virtual quote and customers address.
     *
     * @return boolean
     */
    public function validateVirtual()
    {
        if ($this->quote->getIsVirtual() && !$this->quote->getCustomerIsGuest()) {
            $countryId = self::VALIDATE_COUNTRY;
            // get customer addresses list
            $addresses = $this->quote->getCustomer()->getAddresses();
            // get default shipping address for the customer
            $defaultShipping = $this->quote->getCustomer()->getDefaultShipping();
            /** @var $address \Magento\Customer\Api\Data\AddressInterface */
            if ($defaultShipping) {
                foreach ($addresses as $address) {
                    if ($address->getId() == $defaultShipping) {
                        $countryId = $address->getCountryId();
                        break;
                    }
                }
                if ($countryId != self::VALIDATE_COUNTRY) {
                    return false;
                }
            }
        }
        return true;
    }
}
