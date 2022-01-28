<?php
/**
 * Affirm
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm. All rights reserved.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Affirm\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Identify Financing Program for customer
 */
class PaymentMethodIsActive implements ObserverInterface
{
    /**
     * Affirm config model payment
     *
     * @var \Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Stock registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Init
     *
     * @param \Affirm\Model\Config                         $configAffirm
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Affirm\Model\Config $configAffirm,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->affirmPaymentConfig = $configAffirm;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $result */
        $result = $observer->getEvent()->getResult();
        if (!$result->getData('is_available')) {
            return;
        }

        /** @var \Magento\Payment\Model\Method\Adapter $paymentMethod */
        $paymentMethod = $observer->getEvent()->getMethodInstance();
        if ($paymentMethod->getCode() != \Affirm\Model\Ui\ConfigProvider::CODE) {
            return;
        }

        if (!$this->affirmPaymentConfig->isDisabledForBackorderedItems()) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $stockItem = $this->stockRegistry->getStockItem($quoteItem->getProductId());
            if ($stockItem->getBackorders() && (($stockItem->getQty() - $quoteItem->getQty()) < 0)) {
                $result->setData('is_available', false);
                return;
            }
        }
    }
}
