<?php
/**
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
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
        $result['learnMore'] = '';
        $totals = $this->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) ? $totals['subtotal']->getValue() : 0;
        if($subtotal > (float)$this->affirmPaymentConfig->getAsLowAsMinMpp()) {
            $result['mfpValue'] = $this->asLowAsHelper->getFinancingProgramValue();
        }
        $result['learnMore'] = $this->asLowAsHelper->isVisibleLearnmore() ? 'true' :'false';
        $result['allow_affirm_quote_aslowas'] = $this->affirmPaymentHelper->isAffirmAvailable();
        return $result;
    }
}