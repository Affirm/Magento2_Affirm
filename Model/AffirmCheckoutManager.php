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

use Astound\Affirm\Api\AffirmCheckoutManagerInterface;
use Astound\Affirm\Gateway\Helper\Util;
use Astound\Affirm\Helper\FinancingProgram;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Astound\Affirm\Model\Config as Config;
use Astound\Affirm\Logger\Logger;

/**
 * Class AffirmCheckoutManager
 *
 * @package Astound\Affirm\Model
 */
class AffirmCheckoutManager implements AffirmCheckoutManagerInterface
{

    /**
     * Gift card id cart key
     *
     * @var string
     */
    const ID = 'i';

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
     * Module resource
     *
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * Affirm financing program helper
     *
     * @var \Astound\Affirm\Helper\FinancingProgram
     */
    protected $helper;

    /**
     * Affirm config model
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmConfig;

    /**
     * Affirm logging instance
     *
     * @var \Astound\Affirm\Logger\Logger
     */
    protected $logger;

    /**
     * Initialize affirm checkout
     *
     * @param Session                                    $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductMetadataInterface                   $productMetadata
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     * @param ObjectManagerInterface                     $objectManager
     * @param FinancingProgram $helper
     * @param Config                                     $affirmConfig
     * @param Logger                                     $logger
     */
    public function __construct(
        Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        ObjectManagerInterface $objectManager,
        FinancingProgram $helper,
        Config $affirmConfig,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $this->checkoutSession->getQuote();
        $this->quoteRepository = $quoteRepository;
        $this->productMetadata = $productMetadata;
        $this->moduleResource = $moduleResource;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->affirmConfig = $affirmConfig;
        $this->logger = $logger;
    }

    /**
     * Init checkout and get retrieve increment id
     * form affirm checkout
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initCheckout()
    {
        // collection totals before submit
        $this->quote->collectTotals();
        $this->quote->reserveOrderId();
        $orderIncrementId = $this->quote->getReservedOrderId();
        $discountAmount = $this->quote->getBaseSubtotal() - $this->quote->getBaseSubtotalWithDiscount();
        $shippingAddress = $this->quote->getShippingAddress();

        $response = [];
        if ($discountAmount > 0.001) {
            $discountDescription = $shippingAddress->getDiscountDescription();
            $discountDescription = ($discountDescription) ? sprintf(__('Discount (%s)'), $discountDescription) :
                sprintf(__('Discount'));
            $response['discounts'][$discountDescription] = [
                'discount_amount' => Util::formatToCents($discountAmount)
            ];
        }

        try {
            $country = $this
                ->quote
                ->getBillingAddress()
                ->getCountry();
            $result = $this->quote
                ->getPayment()
                ->getMethodInstance()
                ->canUseForCountry($country);
            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Your billing country isn\'t allowed by Affirm.')
                );
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($orderIncrementId) {
            $this->quoteRepository->save($this->quote);
            $response['order_increment_id'] = $orderIncrementId;
        }
        if ($this->productMetadata->getEdition() == 'Enterprise') {
            $giftWrapperItemsManager = $this->objectManager->create('Astound\Affirm\Api\GiftWrapManagerInterface');
            $wrapped = $giftWrapperItemsManager->getWrapItems();
            if ($wrapped) {
                $response['wrapped_items'] = $wrapped;
            }
            $giftCards = $this->quote->getGiftCards();
            if ($giftCards) {
                $giftCards = json_decode($giftCards);
                foreach ($giftCards as $giftCard) {
                    $giftCardDiscountDescription = sprintf(__('Gift Card (%s)'), $giftCard[self::ID]);
                    $response['discounts'][$giftCardDiscountDescription] = [
                        'discount_amount' => Util::formatToCents($giftCard[self::AMOUNT])
                    ];
                }
            }
        }


        $itemTypes = array();
        $items = $this->quote->getAllVisibleItems();
        foreach ($items as $item) {
            $itemTypes[] = $item->getProductType();
        }

        $response['product_types'] = array_unique($itemTypes);

        $productType = true;
        if (count($response['product_types'])  == 1 && ($response['product_types'][0] == 'downloadable' || $response['product_types'][0] == 'virtual'   )   ) {
            $productType = false;
        }


        $response['address'] = [
            'shipping' => $productType ? $this->getShippingAddress() :  $this->getBillingAddress(),
            'billing' => $this->getBillingAddress()
        ];

        $response['metadata'] = [
            'platform_type' => $this->productMetadata->getName() . ' 2',
            'platform_version' => $this->productMetadata->getVersion() . ' ' . $this->productMetadata->getEdition(),
            'platform_affirm' => $this->moduleResource->getDbVersion('Astound_Affirm'),
            'mode' => $this->affirmConfig->getCheckoutFlowType()
        ];
        $financingProgramValue = $this->helper->getFinancingProgramValue();
        if ($financingProgramValue) {
            $response['financing_program'] = $financingProgramValue;
        }
        $log = [];
        $log['response'] = $response;
        $this->logger->debug('Astound\Affirm\Model\AffirmCheckoutManager::initCheckout', $log);
        return json_encode($response);
    }

    private function getShippingAddress(){
        $shippingAddress =  $this->quote->getShippingAddress();
        $shippingObject = array(
            'name' => array(
                'full_name' => $shippingAddress->getName(),
                'first' => $shippingAddress->getFirstname(),
                'last' => $shippingAddress->getLastname()
            ),
            'address'=>array(
                'line' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegionCode() ? $shippingAddress->getRegionCode() : $this->getRegionCode($shippingAddress->getRegionId()),
                'postcode' =>$shippingAddress->getPostcode(),
                'country' =>$shippingAddress->getCountryId()
            )
        );
        return $shippingObject;
    }

    private function getBillingAddress(){
        $billingAddress =  $this->quote->getBillingAddress();
        $billingObject = array(
            'name' => array(
                'full_name' => $billingAddress->getName(),
                'first' => $billingAddress->getFirstname(),
                'last' => $billingAddress->getLastname()
            ),
            'address'=>array(
                'line' => $billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegionCode() ? $billingAddress->getRegionCode() : $this->getRegionCode($billingAddress->getRegionId()),
                'postcode' =>$billingAddress->getPostcode(),
                'country' =>$billingAddress->getCountryId()
            )
        );

        return $billingObject;

    }

    private function getRegionCode( $regionID ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $region = $objectManager->create('Magento\Directory\Model\Region')
            ->load($regionID);

        return $region->getCode();
    }
}
