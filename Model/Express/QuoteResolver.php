<?php
namespace Astound\Affirm\Model\Express;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolves the active quote from Affirm's order_id (the quote's reserved order id, sent as
 * order_increment_id when the checkout object was built, see AffirmCheckoutManager::initCheckout).
 */
class QuoteResolver
{
    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $orderId
     * @param string $currency ISO 4217
     * @return \Magento\Quote\Model\Quote
     * @throws ExpressException
     */
    public function resolve($orderId, $currency)
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('reserved_order_id', $orderId)
            ->addFilter('is_active', 1)
            ->setPageSize(1)
            ->create();
        $quotes = $this->cartRepository->getList($criteria)->getItems();
        $quote = $quotes ? reset($quotes) : null;

        // The quote must belong to the store the callback URL was served from
        if (!$quote || (int)$quote->getStoreId() !== (int)$this->storeManager->getStore()->getId()) {
            throw new ExpressException(ExpressException::ORDER_NOT_FOUND, 'No active order found for order_id');
        }

        $allowed = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);
        if (strtoupper($currency) !== strtoupper((string)$quote->getQuoteCurrencyCode())
            || !in_array(strtoupper($currency), $allowed, true)
        ) {
            throw new ExpressException(
                ExpressException::CURRENCY_MISMATCH,
                'Currency is not supported for this order',
                ['currency']
            );
        }
        return $quote;
    }
}
