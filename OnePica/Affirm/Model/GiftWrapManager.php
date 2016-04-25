<?php
/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace OnePica\Affirm\Model;

use OnePica\Affirm\Api\GiftWrapManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\GiftWrapping\Api\WrappingRepositoryInterface;
use OnePica\Affirm\Gateway\Helper\Util;

/**
 * Class GiftWrapManager
 *
 * @package OnePica\Affirm\Model
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
     * Wrapped items
     *
     * @var array
     */
    protected $items = [];

    /**
     * Inject wrap model and session objects
     *
     * @param Session                     $checkoutSession
     * @param WrappingRepositoryInterface $wrappingRepository
     */
    public function __construct(
        Session $checkoutSession,
        WrappingRepositoryInterface $wrappingRepository
    ) {
        $this->session = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
        $this->wrappingRepository = $wrappingRepository;
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
        // TODO: Implement getPrintedCardItem() method.
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
        if ($wrapItem) {
            return [
                "display_name"   => $wrapItem->getDesign(),
                "sku"            => "gift-" . $wrapItem->getWrappingId(),
                "unit_price"     => Util::formatToCents($wrapItem->getBasePrice()),
                "qty"            => 1,
                "item_image_url" => $wrapItem->getImageUrl(),
                "item_url"       => $wrapItem->getImageUrl()
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
        return $data;
    }
}
