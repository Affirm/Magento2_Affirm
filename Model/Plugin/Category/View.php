<?php
namespace Astound\Affirm\Model\Plugin\Category;

/**
 * Class View
 *
 * @package Astound\Affirm\Model\Plugin\Category
 */
class View extends ViewAbstract
{

    /**
     * @param $subject
     * @param string $productListHtml
     * @return string
     */
    public function afterGetProductListHtml($subject, $productListHtml)
    {
        if (!$this->affirmPaymentConfig->isAsLowAsEnabled('plp')) {
            return $productListHtml;
        }

        $productListHtml .= '<span data-mage-init=\'{"Astound_Affirm/js/aslowasPLP": ' . $this->getWidgetData() . '}\'></span>';

        return $productListHtml;
    }
}
