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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Astound\Affirm\Model\Config as Config;

/**
 * Financing program helper
 *
 * @package Astound\Affirm\Helper
 */
class FinancingProgram
{
    /**
     * Current checkout quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**#@+
     * Financing program entities values
     */
    protected $customerFP;
    protected $productFP;
    protected $categoryFP;
    protected $entityFP;
    protected $cartSizeFP;
    /**#@-*/

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Locale date
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $products;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Init
     *
     * @param Session $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $configAffirm
     * @param TimezoneInterface $localeDate
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Session $session,
        \Magento\Customer\Model\Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Config $configAffirm,
        TimezoneInterface $localeDate,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->quote = $session->getQuote();
        $this->scopeConfig = $scopeConfig;
        $this->affirmPaymentConfig = $configAffirm;
        $this->_localeDate = $localeDate;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Get MFP default
     *
     * @return string
     */
    public function getFinancingProgramDefault()
    {
        return $this->affirmPaymentConfig->getConfigData('financing_program_value_default');
    }

    /**
     * Get MFP for date range
     *
     * @return string
     */
    public function getFinancingProgramDateRange()
    {
        return $this->affirmPaymentConfig->getConfigData('financing_program_value');
    }

    /**
     * Get mfp start date
     *
     * @return string
     */
    public function getFinancingProgramStartDate()
    {
        return $this->affirmPaymentConfig->getConfigData('start_date_mfp');
    }

    /**
     * Get mfp end date
     *
     * @return string
     */
    public function getFinancingProgramEndDate()
    {
        return $this->affirmPaymentConfig->getConfigData('end_date_mfp');
    }

    /**
     * Get MFP cart size default
     *
     * @return string
     */
    public function getFinancingProgramCartSizeValue()
    {
        return $this->affirmPaymentConfig->getConfigData('financing_program_cart_size_value');
    }

    /**
     * Get MFP cart size min order total
     *
     * @return string
     */
    public function getFinancingProgramCartSizeMinOrderTotal()
    {
        return $this->affirmPaymentConfig->getConfigData('financing_program_cart_size_min_order_total');
    }

    /**
     * Get MFP cart size max order total
     *
     * @return string
     */
    public function getFinancingProgramCartSizeMaxOrderTotal()
    {
        return $this->affirmPaymentConfig->getConfigData('financing_program_cart_size_max_order_total');
    }

    /**
     * Is MFP valid for current date
     *
     * @return bool
     */
    protected function isFinancingProgramValidCurrentDate()
    {
        return $this->getFinancingProgramDateRange() &&
            $this->_localeDate->isScopeDateInInterval(null, $this->getFinancingProgramStartDate(),
                $this->getFinancingProgramEndDate()
            );
    }

    /**
     * Get customer financing program
     *
     * @return string
     */
    protected function getCustomerFinancingProgram()
    {
        if (null === $this->customerFP) {
            if ($this->customerSession->isLoggedIn()) {
                $this->customerFP = $this->customerSession->getCustomer()->getAffirmCustomerMfp();
            } else {
                $this->customerFP =  $this->customerSession->getAffirmCustomerMfp();
            }
            $this->customerFP = ($this->customerFP) ? $this->customerFP : '';
        }
        return $this->customerFP;
    }

    /**
     * Get products from quote
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getQuoteProductCollection()
    {
        if (null === $this->products) {
            $visibleQuoteItems = $this->quote->getAllVisibleItems();
            $productIds = [];
            foreach ($visibleQuoteItems as $visibleQuoteItem) {
                $productIds[] = $visibleQuoteItem->getProductId();
            }
            $this->products = $this->productCollectionFactory->create()
                ->addAttributeToSelect(['affirm_product_mfp', 'affirm_product_mfp_type', 'affirm_product_mfp_priority'])
                ->addAttributeToFilter('entity_id', array('in' => $productIds));
        }
        return $this->products;
    }

    /**
     * Get categories from quote products
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getQuoteCategoryCollection()
    {
        $categoryItemsIds = [];
        $productCollection = $this->getQuoteProductCollection();
        foreach ($productCollection as $product) {
            $categoryIds = $product->getCategoryIds();
            if (!empty($categoryIds)) {
                $categoryItemsIds = array_merge($categoryItemsIds, $categoryIds);
            }
        }
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['affirm_category_mfp', 'affirm_category_mfp_type', 'affirm_category_mfp_priority'])
            ->addAttributeToFilter('entity_id', array('in' => $categoryItemsIds))
            ->addAttributeToFilter('affirm_category_mfp', array('neq' => ''))
            ->addAttributeToFilter('affirm_category_mfp', array('notnull' => true));
        return $categoryCollection;
    }

    /**
     * Get financing program from product or category entity items
     *
     * @param array $entityItems
     * @return string
     */
    protected function getFinancingProgramFromEntityItems(array $entityItems)
    {
        $exclusiveMFP = array();
        $inclusiveMFP = array();
        $existItemWithoutMFP = false;
        $inclusiveMFPTemp = array();
        $this->entityFP = '';
        foreach ($entityItems as $entityItemMFP) {
            if (!$entityItemMFP['value']) {
                $existItemWithoutMFP = true;
            } else {
                if (!$entityItemMFP['type']) {
                    if (!in_array($entityItemMFP['value'], $exclusiveMFP)) {
                        $exclusiveMFP[] = $entityItemMFP['value'];
                    }
                } else {
                    if (!in_array($entityItemMFP['value'], $inclusiveMFPTemp)) {
                        $inclusiveMFPTemp[] = $entityItemMFP['value'];
                        $inclusiveMFP[] = array(
                            'value'    => $entityItemMFP['value'],
                            'priority' => $entityItemMFP['priority']
                        );
                    }
                }
            }
        }
        if (count($inclusiveMFP) == 1) {
            $this->entityFP = $inclusiveMFP[0]['value'];
        } elseif ((count($exclusiveMFP) == 1) && (count($inclusiveMFP) == 0) && !$existItemWithoutMFP) {
            $this->entityFP = $exclusiveMFP[0];
        } elseif (count($inclusiveMFP) > 1) {
            $higherPriority = -1;
            foreach ($inclusiveMFP as $inclusiveMFPValue) {
                if ($inclusiveMFPValue['priority'] > $higherPriority) {
                    $higherPriority = $inclusiveMFPValue['priority'];
                    $this->entityFP = $inclusiveMFPValue['value'];
                }
            }
        } else {
            $this->entityFP = '';
        }
        return $this->entityFP;
    }

    /**
     * Convert product collection into items array
     *
     * @param \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
     * @return array
     */
    protected function convertProductCollectionToItemsArray(
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        $entityItems = [];
        foreach ($collection as $entity) {
            $entityItems[] = [
                'value'    => $entity->getAffirmProductMfp(),
                'type'     => $entity->getAffirmProductMfpType(),
                'priority' => $entity->getAffirmProductMfpPriority() ?: 0
            ];
        }
        return $entityItems;
    }

    /**
     * Convert product collection into items array
     *
     * @param \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
     * @return array
     */
    protected function convertCategoryCollectionToItemsArray(
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        $entityItems = [];
        foreach ($collection as $entity) {
            $entityItems[] = [
                'value'    => $entity->getAffirmCategoryMfp(),
                'type'     => $entity->getAffirmCategoryMfpType(),
                'priority' => $entity->getAffirmCategoryMfpPriority() ?: 0
            ];
        }
        return $entityItems;
    }

    /**
     * Get financing program from products
     *
     * @return string
     */
    protected function getFinancingProgramFromProducts()
    {
        if (null === $this->productFP) {
            $productCollection = $this->getQuoteProductCollection();
            $entityItems = $this->convertProductCollectionToItemsArray($productCollection);
            $this->productFP = $this->getFinancingProgramFromEntityItems($entityItems);
        }
        return $this->productFP;
    }

    /**
     * Get financing program from categories
     *
     * @return string
     */
    protected function getFinancingProgramFromCategories()
    {
        if (null === $this->categoryFP) {
            $categoryCollection = $this->getQuoteCategoryCollection();
            $entityItems = $this->convertCategoryCollectionToItemsArray($categoryCollection);
            $this->categoryFP = $this->getFinancingProgramFromEntityItems($entityItems);
        }
        return $this->categoryFP;
    }

    /**
     * Get Quote base grand total
     *
     * @return float
     */
    protected function getQuoteBaseGrandTotal()
    {
        return $this->quote->getBaseGrandTotal();
    }

    /**
     * Get financing program from cart size
     *
     * @return string
     */
    protected function getFinancingProgramFromCartSize()
    {
        if (null === $this->cartSizeFP) {
            $cartTotal = $this->getQuoteBaseGrandTotal();
            $minTotal = $this->getFinancingProgramCartSizeMinOrderTotal();
            $maxTotal = $this->getFinancingProgramCartSizeMaxOrderTotal();
            $cartSizeValue = $this->getFinancingProgramCartSizeValue();

            if ($cartSizeValue && !empty($minTotal) && !empty($maxTotal)
                && $cartTotal >= $minTotal
                && $cartTotal <= $maxTotal
            ) {
                $this->cartSizeFP = $cartSizeValue;
            } else {
                $this->cartSizeFP = '';
            }
        }
        return $this->cartSizeFP;
    }

    /**
     * Get financing program value
     *
     * @return string
     */
    public function getFinancingProgramValue()
    {
        $dynamicallyMFPValue = $this->getCustomerFinancingProgram();
        if (!empty($dynamicallyMFPValue)) {
            return $dynamicallyMFPValue;
        } elseif ($this->getFinancingProgramFromProducts()) {
            return $this->getFinancingProgramFromProducts();
        } elseif ($this->getFinancingProgramFromCategories()) {
            return $this->getFinancingProgramFromCategories();
        } elseif ($this->getFinancingProgramFromCartSize()) {
            return $this->getFinancingProgramFromCartSize();
        } elseif ($this->isFinancingProgramValidCurrentDate()) {
            return $this->getFinancingProgramDateRange();
        } else {
            return $this->getFinancingProgramDefault();
        }
    }
}
