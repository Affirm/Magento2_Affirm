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

namespace Astound\Affirm\Block\Promotion\ProductPage;

use Astound\Affirm\Block\Promotion\AslowasAbstract;

/**
 * Class AsLowAs
 *
 * @package Astound\Affirm\Block\Promotion\ProductPage
 */
class Aslowas extends AslowasAbstract
{
    /**
     * As low as data
     *
     * @var array
     */
    protected $data = ['logo', 'script', 'public_api_key', 'min_order_total', 'max_order_total',
            'selector', 'currency_rate', 'backorders_options', 'element_id', 'country_code', 'locale'];

    /**
     * Validate block before showing on front
     * Specify validation for product "As Low As" logic.
     *
     * @return bool|void
     */
    public function validate()
    {
        $product = $this->affirmPaymentHelper->getProduct();
        if ($this->affirmPaymentConfig->getConfigData('active')
                && $this->affirmPaymentHelper->isAffirmAvailableForProduct($product)
        ) {
                if ((float)$product->getFinalPrice() < (float)$this->affirmPaymentConfig->getAsLowAsMinMpp()) {
                    return false;
                }
            return true;
        }
        return false;
    }

    /**
     * Add selector data to the block context.
     * This needs for bundle product, because bundle has
     * different structure.
     */
    public function process()
    {
        if ($this->type && $this->type == 'bundle') {
            $this->setData('selector', '.bundle-info');
        } else {
            $this->setData('selector', '.product-info-main');
        }
        if (!$this->affirmPaymentConfig->isCurrentStoreCurrencyUSD()) {
            $rate = $this->affirmPaymentConfig->getUSDCurrencyRate();
            if ($rate) {
                $this->setData('currency_rate', $rate);
            }
        }
        $product = $this->affirmPaymentHelper->getProduct();
        $this->setData(
                'backorders_options',
                $this->affirmPaymentHelper->getConfigurableProductBackordersOptions($product)
        );
        $this->setData('element_id', 'als_pdp');

        parent::process();
    }

    /**
     * get MFP value for current product
     * @return string
     */
    public function getMFPValue()
    {
        $productCollection = $this->affirmPaymentHelper->getProduct()->getCollection()
            ->addAttributeToSelect(['affirm_product_promo_id', 'affirm_product_mfp_type', 'affirm_product_mfp_priority', 'affirm_product_mfp_start_date', 'affirm_product_mfp_end_date'])
            ->addAttributeToFilter('entity_id', $this->affirmPaymentHelper->getProduct()->getId());

        return $this->asLowAsHelper->getFinancingProgramValueALS($productCollection);
    }

    /**
     * Get product id on PDP
     *
     * @return Mage_Catalog_Model_Product|null
     */
    public function getProductId()
    {
        return $this->affirmPaymentHelper->getProduct()->getId();
    }

    public function getLearnMoreValue(){
        return $this->asLowAsHelper->isVisibleLearnmore() ? 'true' :'false';
    }
}
