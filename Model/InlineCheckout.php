<?php
namespace Astound\Affirm\Model;

use Astound\Affirm\Gateway\Helper\Util;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Api\InlineCheckoutInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\QuoteValidator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InlineCheckout
 *
 * @package Astound\Affirm\Model
 */
class InlineCheckout implements InlineCheckoutInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     */
    private $quote = null;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Astound\Affirm\Gateway\Helper\Util
     */
    private $util;

    /**
     * @var QuoteValidator
     */
    private $quoteValidator;

    public function __construct(
        Session $checkoutSession,
        UrlInterface $urlInterface,
        ResourceInterface $moduleResource,
        ProductMetadataInterface $productMetadata,
        Util $util,
        QuoteValidator $quoteValidator
    ){
        $this->session = $checkoutSession;
        $this->urlBuilder = $urlInterface;
        $this->moduleResource = $moduleResource;
        $this->productMetadata = $productMetadata;
        $this->util = $util;
        $this->quoteValidator = $quoteValidator;
    }

    /**
     * Return current quote from checkout session.
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote(){
        if(null == $this->quote){
            $this->quote = $this->session->getQuote();
        }
        return $this->quote;
    }

    public function initInline(){
        $quote = $this->getQuote();
        $quote->collectTotals();

        if(!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
        }

        try{
            $this->quoteValidator->validateBeforeSubmit($quote);
        } catch (LocalizedException $e) {
            return json_encode(array(
                'merchant' => array(
                    'user_confirmation_url'        => $this->urlBuilder
                        ->getUrl('affirm/payment/confirm', ['_secure' => true]),
                    'user_cancel_url'              => $this->urlBuilder
                        ->getUrl('affirm/payment/cancel', ['_secure' => true]),
                    'user_confirmation_url_action' => 'POST',
                ),
                'order_id' => $quote->getReservedOrderId(),
                'total'    => $this->util->formatToCents($quote->getGrandTotal())
            ));

        }catch (\Exception $e) {

        }

        $checkoutObject = array(
            'merchant' => array(
                'user_confirmation_url'        => $this->urlBuilder
                    ->getUrl('affirm/payment/confirm', ['_secure' => true]),
                'user_cancel_url'              => $this->urlBuilder
                    ->getUrl('affirm/payment/cancel', ['_secure' => true]),
                'user_confirmation_url_action' => 'POST',
            ),
            'order_id' => $quote->getReservedOrderId(),
            'shipping_amount' => $this->util->formatToCents($quote->getShippingAddress()->getShippingAmount()),
            'total'    => $this->util->formatToCents($quote->getGrandTotal()),
            'tax_amount' => $this->util->formatToCents($quote->getShippingAddress()->getTaxAmount()),
            'metadata' => array(
                'platform_type'    => $this->productMetadata->getName() . ' 2',
                'platform_version' => $this->productMetadata->getVersion() . ' ' . $this->productMetadata->getEdition(),
                'platform_affirm'  => $this->moduleResource->getDbVersion('Astound_Affirm'),
                'mode'             => 'inline'
            )
        );

        if($items = $this->formatItems($quote->getAllVisibleItems())) {
            $checkoutObject['items'] = $items;
        }

        if ($shippingAddress = $this->formatAddress($quote->getShippingAddress())) {
            $checkoutObject['shipping'] = $shippingAddress;
        }

        if ($billingAddress = $this->formatAddress($quote->getBillingAddress())) {
            $checkoutObject['billing'] = $billingAddress;
        }

        $discountAmount = $this->getQuote()->getBaseSubtotal() - $this->getQuote()->getBaseSubtotalWithDiscount();
        if ($discountAmount > 0.001) {
            $checkoutObject['discounts']['discount'] = [
                'discount_amount' => Util::formatToCents($discountAmount)
            ];
        }

        if ($this->productMetadata->getEdition() == 'Enterprise') {
            $giftWrapperItemsManager = ObjectManager::getInstance()->create('Astound\Affirm\Api\GiftWrapManagerInterface');
            $wrapped = $giftWrapperItemsManager->getWrapItems();
            if ($wrapped) {
                $checkoutObject['wrapped_items'] = $wrapped;
            }
            $giftCards = $this->getQuote()->getGiftCards();
            if ($giftCards) {
                $giftCards = json_decode($giftCards);
                foreach ($giftCards as $giftCard) {
                    $giftCardDiscountDescription = sprintf(__('Gift Card (%s)'), $giftCard[AffirmCheckoutManager::ID]);
                    $checkoutObject['discounts'][$giftCardDiscountDescription] = [
                        'discount_amount' => Util::formatToCents($giftCard[AffirmCheckoutManager::AMOUNT])
                    ];
                }
            }
        }

        return json_encode($checkoutObject);
    }

    private function formatAddress($address){
        $formattedAddress = false;
        if($address->getCity()) {
            $street = $address->getStreet();
            $formattedAddress = array(
                'name' => array(
                    'first' => $address->getFirstName(),
                    'last'  => $address->getLastName(),
                ),
                'address' => array(
                    'line1'   => isset($street[0]) ? $street[0] : '',
                    'line2'   => isset($street[1]) ? $street[1] : '',
                    'city'    => $address->getCity(),
                    'state'   => $address->getRegion(),
                    'zipcode' => $address->getPostcode(),
                    'country' => $address->getCountryId()
                ),
                'phone_number' => $address->getTelephone() ? $address->getTelephone() : '',
                'email'        => $address->getEmail() ? $address->getEmail() : ''
            );
        }

        return $formattedAddress;
    }

    private function formatItems($items) {
        $formattedItems = array();
        foreach ( (object)$items as $item) {
            if( is_object($item)){
                $product = $item->getProduct();

                $formattedItems[] = array(
                    'display_name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'unit_price' => $item->getPrice(),
                    'qty' => $item->getQty(),
                    'item_image_url' => $product->getData('thumbnail'),
                    'item_url' => $product->getUrlModel()->getUrl($product)
                );
            }
        }
        return $formattedItems;
    }
}
