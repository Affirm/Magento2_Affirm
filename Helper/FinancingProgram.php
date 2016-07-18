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
            foreach ($visibleQuoteItems as $visibleQuoteItem) {
                $productIds[] = $visibleQuoteItem->getProductId();
            }
            $this->products = $this->productCollectionFactory->create()
                ->addAttributeToSelect('affirm_product_mfp')
                ->addAttributeToFilter('entity_id', array('in' => $productIds));
        }
        return $this->products;
    }

    /**
     * Get financing program from products
     *
     * @return string
     */
    protected function getFinancingProgramFromProducts()
    {
        if (null === $this->productFP) {
            $productItemsMFP = [];
            $productCollection = $this->getQuoteProductCollection();
            foreach ($productCollection as $product) {
                if ($product->getAffirmProductMfp()) {
                    $productItemsMFP[] = $product->getAffirmProductMfp();
                }
            }
            if (!empty($productItemsMFP) && (count(array_unique($productItemsMFP)) == 1)) {
                $this->productFP = reset($productItemsMFP);
            } else{
                $this->productFP = '';
            }
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
            $categoryItemsIds = [];
            $categoryItemsMFP = [];
            $productCollection = $this->getQuoteProductCollection();
            foreach ($productCollection as $product) {
                $categoryIds = $product->getCategoryIds();
                if (!empty($categoryIds)) {
                    $categoryItemsIds = array_merge($categoryItemsIds, $categoryIds);
                }
            }
            $categoryCollection = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('affirm_category_mfp')
                ->addAttributeToFilter('entity_id', array('in' => $categoryItemsIds));
            foreach ($categoryCollection as $category) {
                if ($category->getAffirmCategoryMfp()) {
                    $categoryItemsMFP[] = $category->getAffirmCategoryMfp();
                }
            }
            if (!empty($categoryItemsMFP) && (count(array_unique($categoryItemsMFP)) == 1)) {
                $this->categoryFP = reset($categoryItemsMFP);
            } else {
                $this->categoryFP = '';
            }
        }
        return $this->categoryFP;
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
        } elseif ($this->isFinancingProgramValidCurrentDate()) {
            return $this->getFinancingProgramDateRange();
        } else {
            return $this->getFinancingProgramDefault();
        }
    }
}
