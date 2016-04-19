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

/**
 * Class AffirmCheckoutManager
 *
 * @package OnePica\Affirm\Model
 */
class AffirmCheckoutManager implements AffirmCheckoutManagerInterface
{
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
     * Inject session and cart repository data.
     *
     * @param Session                                    $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $this->checkoutSession->getQuote();
        $this->quoteRepository = $quoteRepository;
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
        $discountAmount = $this->quote->getSubtotal() - $this->quote->getSubtotalWithDiscount();
        $response = [];
        if ($discountAmount > 0.001) {
            $shippingAddress = $this->quote->getShippingAddress();
            $discountDescription = $shippingAddress->getDiscountDescription();
            $response['discounts'] = [
                $discountDescription => [
                    'discount_amount' => Util::formatToCents($discountAmount)
                ]
            ];
        }
        if ($orderIncrementId) {
            $this->quoteRepository->save($this->quote);
            $response['order_increment_id'] = $orderIncrementId;
        }
        return json_encode($response);
    }
}
