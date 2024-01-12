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

namespace Astound\Affirm\Model;

use Astound\Affirm\Api\GiftWrapManagerInterface;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Gateway\Helper\Util;
use Astound\Affirm\Helper\Payment as PaymentHelper;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class GiftWrapManager
 *
 * @package Astound\Affirm\Model
 */
class GiftWrapManager implements GiftWrapManagerInterface
{
    /**
     * Current checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Current quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Wrapping repository class
     *
     * @var \Magento\GiftWrapping\Api\WrappingRepositoryInterface
     */
    protected $wrappingRepository;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Wrapped items
     *
     * @var array
     */
    protected $items = [];

    /**
     * Image helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Gift wrap manager init
     *
     * @param Session                $checkoutSession
     * @param ObjectManagerInterface $objectManager
     * @param PaymentHelper          $paymentHelper
     */
    public function __construct(
        Session $checkoutSession,
        ObjectManagerInterface $objectManager,
        PaymentHelper $paymentHelper
    ) {
        $this->session = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
        $this->wrappingRepository = $objectManager->create('Magento\GiftWrapping\Api\WrappingRepositoryInterface');
        $this->imageHelper = $paymentHelper;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->quote->getStoreId();
    }

    /**
     * Get Printed card item
     *
     * @return mixed
     */
    public function getPrintedCardItem()
    {
        $isApplicable = $this->quote->getGwAddCard();
        if ($isApplicable) {
            $printedCardPrice = $this->quote->getGwCardBasePrice();
            if ($printedCardPrice) {
                return [
                    "display_name"   => "Printed Card",
                    "sku"            => "printed-card",
                    "unit_price"     => Util::formatToCents($this->quote->getGwCardBasePrice()),
                    "qty"            => 1,
                    "item_image_url" => $this->imageHelper->getPlaceholderImage(),
                    "item_url"       => $this->imageHelper->getPlaceholderImage()
                ];
            }
        }
    }

    /**
     * Get all wrap items
     *
     * @return mixed
     */
    public function getWrapItems()
    {
        $wrappedIdForOrder = $this->quote->getGwId();
        $data = [];
        if ($wrappedIdForOrder) {
            /** @var \Magento\GiftWrapping\Api\Data\WrappingInterface  $wrapItem */
            $wrapItem = $this->wrappingRepository->get($wrappedIdForOrder, $this->getStoreId());
            $data[$wrapItem->getWrappingId()] = $this->processItemData($wrapItem);
        }
        // If order contains wraps for item
        $res = $this->prepareWrapForItems($data);
        if ($res) {
            $this->items = array_values($res);
        }
        return $this->items;
    }

    /**
     * Process item data
     *
     * @param \Magento\GiftWrapping\Api\Data\WrappingInterface $wrapItem
     * @return array
     */
    protected function processItemData($wrapItem)
    {
        if ($wrapItem && $wrapItem->getBasePrice()) {
            return [
                "display_name"   => $wrapItem->getDesign(),
                "sku"            => "gift-wrap-" . $wrapItem->getWrappingId(),
                "unit_price"     => Util::formatToCents($wrapItem->getBasePrice()),
                "qty"            => 1,
                "item_image_url" => $wrapItem->getImageUrl() ? $wrapItem->getImageUrl():
                    $this->imageHelper->getPlaceholderImage(),
                "item_url"       => $wrapItem->getImageUrl()? $wrapItem->getImageUrl():
                    $this->imageHelper->getPlaceholderImage()
            ];
        }
        return [];
    }

    /**
     * Find wrap items for concrete product(s)
     *
     * @param array $data
     * @return mixed
     */
    protected function prepareWrapForItems($data)
    {
        $quoteItems = $this->quote->getAllItems();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quoteItems as $item) {
            $wrappedItemId = $item->getGwId();
            if ($wrappedItemId) {
                if (isset($data[$wrappedItemId]) && $data[$wrappedItemId]) {
                    $data[$wrappedItemId]["qty"]++;
                } else {
                    $wrapItem = $this->wrappingRepository->get($wrappedItemId, $this->getStoreId());
                    $data[$wrappedItemId] = $this->processItemData($wrapItem);
                }
            }
        }
        if ($this->getPrintedCardItem()) {
            $data[] = $this->getPrintedCardItem();
        }
        return $data;
    }
}
