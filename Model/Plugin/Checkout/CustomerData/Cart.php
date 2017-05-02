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

namespace Astound\Affirm\Model\Plugin\Checkout\CustomerData;

use Astound\Affirm\Model\Config as Config;
use Astound\Affirm\Helper\AsLowAs;
use Astound\Affirm\Helper\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Checkout\Model\Session;

class Cart
{
    /**
     * Affirm payment model instance
     *
     * @var Payment
     */
    protected $affirmPaymentHelper;

    /**
     * AsLowAs helper
     *
     * @var Config
     */
    protected $asLowAsHelper;

    /**
     * Affirm config model payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;


    /**
     * @param \Astound\Affirm\Helper\Payment    $helperAffirm
     * @param StoreManagerInterface             $storeManagerInterface
     * @param Config                            $configAffirm
     * @param AsLowAs                           $asLowAs
     * @param ProductCollectionFactory          $productCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
            Payment $helperAffirm,
            StoreManagerInterface $storeManagerInterface,
            Config $configAffirm,
            AsLowAs $asLowAs,
            ProductCollectionFactory $productCollectionFactory,
            Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;

        $this->affirmPaymentHelper = $helperAffirm;

        $this->productCollectionFactory = $productCollectionFactory;

        $this->asLowAsHelper = $asLowAs;

        $currentWebsiteId = $storeManagerInterface->getStore()->getWebsiteId();
        $this->affirmPaymentConfig = $configAffirm;
        $this->affirmPaymentConfig->setWebsiteId($currentWebsiteId);
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Check if Affirm method available for current quote items combination
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['mfpValue'] = '';
        $totals = $this->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) ? $totals['subtotal']->getValue() : 0;
        if($subtotal > (float)$this->affirmPaymentConfig->getAsLowAsMinMpp()) {
            $result['mfpValue'] = $this->asLowAsHelper->getFinancingProgramValue();
        }

        $result['allow_affirm_quote_aslowas'] = $this->affirmPaymentHelper->isAffirmAvailable();
        return $result;
    }
}
