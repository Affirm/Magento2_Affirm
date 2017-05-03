<?php
namespace Astound\Affirm\Model\Plugin\Product;

use Astound\Affirm\Model\Plugin\Category\ViewAbstract;

/**
 * Class ListProduct
 *
 * @package Astound\Affirm\Model\Plugin\Product
 */
class ListProduct extends ViewAbstract
{
    /**
     * @param $subject
     * @param $procede
     * @param \Magento\Catalog\Model\Product $product
     * @param string $price
     * @return string
     */
    public function aroundGetProductPrice($subject, $procede, \Magento\Catalog\Model\Product $product)
    {
        $priceHtml = $procede($product);
        if (!$this->affirmPaymentConfig->isAsLowAsEnabled('plp')) {
            return $priceHtml;
        }

        $mpp = $this->getMinMPP();
        $price = $product->getFinalPrice();
        if ($price > $mpp) {
            $productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect(['affirm_product_promo_id', 'affirm_product_mfp_type', 'affirm_product_mfp_priority', 'affirm_product_mfp_start_date', 'affirm_product_mfp_end_date'])
                ->addAttributeToFilter('entity_id', $product->getId());

            $mfpValue = $this->asLowAsHelper->getFinancingProgramValueALS($productCollection);
            $priceHtml .= '<div id="as_low_as_plp_' . $product->getId() . '" class="affirm-as-low-as" ' . $this->getDataAffirmColor() . ' ' . (!empty($mfpValue) ? 'data-promo-id="' . $mfpValue . '"' : '') . ' data-amount="0"></div>';
        }

        return $priceHtml;
    }
}
