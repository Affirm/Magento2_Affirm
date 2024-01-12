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

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product;

/**
 * Financing program helper
 *
 * @package Astound\Affirm\Helper
 */
class AsLowAs extends FinancingProgram
{

    protected $_allRules = null;

    /**
     * Initialization
     *
     */
    protected function _init()
    {
        $this->isALS = true;
    }

    /**
     * Get categories from products
     *
     * @param Product\Collection $productCollection
     *
     * @return Category\Collection
     */
    protected function getCategoryCollection(Product\Collection $productCollection)
    {
        $categoryItemsIds = [];
        $flagProductWithoutMfpCategories = false;
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($productCollection as $product) {
            /** @var Category\Collection $categoryProductCollection */
            $categoryProductCollection = $product->getCategoryCollection();
            $categoryProductCollection
                ->addAttributeToFilter('affirm_category_mfp', array('neq' => ''))
                ->addAttributeToFilter('affirm_category_mfp', array('notnull' => true));
            $categoryIds = $categoryProductCollection->getAllIds();
            if (!empty($categoryIds)) {
                $categoryItemsIds = array_merge($categoryItemsIds, $categoryIds);
            } else {
                $flagProductWithoutMfpCategories = true;
            }
        }
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['affirm_category_mfp', 'affirm_category_mfp_type', 'affirm_category_mfp_priority', 'affirm_category_mfp_start_date', 'affirm_category_mfp_end_date'])
            ->addAttributeToFilter('entity_id', array('in' => $categoryItemsIds));
        if ($flagProductWithoutMfpCategories) {
            $categoryCollection->setFlag('productWithoutMfpCategories', true);
        }
        return $categoryCollection;
    }

    /**
     * Get financing program value
     *
     * @param Product\Collection $productCollection
     *
     * @return string
     */
    public function getFinancingProgramValueALS(Product\Collection $productCollection)
    {
        $dynamicallyMFPValue = $this->getCustomerFinancingProgram();
        if (!empty($dynamicallyMFPValue)) {
            return $dynamicallyMFPValue;
        } elseif ($mfpValue = $this->getFinancingProgramFromProductsALS($productCollection)) {
            return $mfpValue;
        } elseif ($mfpValue = $this->getFinancingProgramFromCategoriesALS($productCollection)) {
            return $mfpValue;
        } elseif ($this->isFinancingProgramValidCurrentDate()) {
            return $this->getFinancingProgramDateRange();
        } else {
            return $this->getFinancingProgramDefault();
        }
    }

    /**
     * Is visible Learn more for ALA
     *
     * @return boolean
     */
    public function isVisibleLearnmore()
    {
        return $this->affirmPaymentConfig->getAsLowAsValue('learn_more');
    }

    /**
     * Returns formated price.
     *
     * @param string $price
     * @param string $currencyCode
     * @return string
     */
    public function formatPrice($price, $currencyCode = '')
    {
        $formatedPrice = number_format($price, 2, '.', '');

        if ($currencyCode) {
            return $formatedPrice . ' ' . $currencyCode;
        } else {
            return $formatedPrice*100;
        }
    }
}
