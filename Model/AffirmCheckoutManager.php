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

use OnePica\Affirm\Api\AffirmCheckoutManagerInterface;
use Magento\Checkout\Model\Session;
use OnePica\Affirm\Gateway\Helper\Util;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
/**
 * Class AffirmCheckoutManager
 *
 * @package OnePica\Affirm\Model
 */
class AffirmCheckoutManager implements AffirmCheckoutManagerInterface
{
    /**
     * Gift card code cart key
     *
     * @var string
     */
    const CODE = 'c';

    /**
     * Gift card amount cart key
     *
     * @var string
     */
    const AMOUNT = 'a';

    /**
     * Injected checkout session
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Injected model quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Injected repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Product metadata
     *
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Initialize affirm checkout
     *
     * @param Session                                    $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductMetadataInterface                   $productMetadata
     * @param ObjectManagerInterface                     $objectManager
     */
    public function __construct(
        Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductMetadataInterface $productMetadata,
        ObjectManagerInterface $objectManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $this->checkoutSession->getQuote();
        $this->quoteRepository = $quoteRepository;
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
    }

    /**
     * Init checkout and get retrieve increment id
     * form affirm checkout
     *
     * @return string
     */
    public function initCheckout()
    {
        // collection totals before submit
        $this->quote->collectTotals();
        $this->quote->reserveOrderId();
        $orderIncrementId = $this->quote->getReservedOrderId();
        $discountAmount = $this->quote->getBaseSubtotal() - $this->quote->getBaseSubtotalWithDiscount();
        $response = [];
        if ($discountAmount > 0.001) {
            $shippingAddress = $this->quote->getShippingAddress();
            $discountDescription = $shippingAddress->getDiscountDescription();
            $discountDescription = ($discountDescription) ? sprintf(__('Discount (%s)'), $discountDescription) :
                sprintf(__('Discount'));
            $response['discounts'][$discountDescription] = [
                'discount_amount' => Util::formatToCents($discountAmount)
            ];
        }

        if ($orderIncrementId) {
            $this->quoteRepository->save($this->quote);
            $response['order_increment_id'] = $orderIncrementId;
        }
        if ($this->productMetadata->getEdition() == 'Enterprise') {
            $giftWrapperItemsManager = $this->objectManager->create('OnePica\Affirm\Api\GiftWrapManagerInterface');
            $wrapped = $giftWrapperItemsManager->getWrapItems();
            if ($wrapped) {
                $response['wrapped_items'] = $wrapped;
            }
            $giftCards = $this->quote->getGiftCards();
            if ($giftCards) {
                $giftCards = unserialize($giftCards);
                foreach ($giftCards as $giftCard) {
                    $giftCardDiscountDescription = sprintf(__('Gift Card (%s)'), $giftCard[self::CODE]);
                    $response['discounts'][$giftCardDiscountDescription] = [
                        'discount_amount' => Util::formatToCents($giftCard[self::AMOUNT])
                    ];
                }
            }
        }
        return json_encode($response);
    }
}
