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

namespace OnePica\Affirm\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Helper\Product as ProductHelper;
use \Magento\Framework\View\Element\Template as BlockTemplate;
use \Magento\Framework\Registry;
/**
 * Class Checkout
 *
 * @package OnePica\Affirm\Block
 */
class Checkout extends BlockTemplate
{
    /**
     * Core registry object
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Injected url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Current quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Product helper for get data about image
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * Inject all needed objects needed for checkout block
     *
     * @param Template\Context                  $context
     * @param \Magento\Framework\Registry       $registry
     * @param UrlInterface                      $urlInterface
     * @param ProductHelper                     $productHelper
     * @param array                             $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        UrlInterface $urlInterface,
        ProductHelper $productHelper,
        array $data = []
    ) {
        $this->urlBuilder = $urlInterface;
        $this->coreRegistry = $registry;
        $this->quote = $this->coreRegistry->registry('current_quote');
        $this->productHelper = $productHelper;
        parent::__construct($context, $data);
    }

    /**
     * Widget options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Get serialized options
     *
     * @return string
     */
    public function getSerializedOptions()
    {
        $orderId = $this->coreRegistry->registry('order_id');
        $shippingAmount = $this->quote
            ->getShippingAddress()
            ->getShippingAmount();
        $taxAmount = $this->quote
            ->getShippingAddress()->getBaseTaxAmount();
        $total = $this->quote->getGrandTotal();

        $this->initLoadScriptData();
        $this->initShipping();
        $this->initItems();
        $this->initConfig();
        $this->initMerchant();
        $this->initAddress();
        $this->initAddress('billing');

        $this->_options['order_id'] = $orderId;
        $this->_options['discounts'] = [];

        // Convert total amounts to cents
        $this->_options['shipping_amount'] = $shippingAmount * 100;
        $this->_options['total'] = $total * 100;
        $this->_options['tax_amount'] = $taxAmount * 100;

        return json_encode($this->_options);
    }

    /**
     * Retrieve script information
     * Depends on mode configuration settings
     */
    protected function initLoadScriptData()
    {
        $this->_options['public_key'] = $this->_scopeConfig
            ->getValue('payment/affirm_gateway/mode') == 'sandbox' ?
            $this->_scopeConfig->getValue('payment/affirm_gateway/public_api_key_sandbox'):
            $this->_scopeConfig->getValue('payment/affirm_gateway/public_api_key_production');
        $this->_options['script'] = $this->_scopeConfig
                ->getValue('payment/affirm_gateway/mode') == 'sandbox' ?
            "https://cdn1-sandbox.affirm.com/js/v2/affirm.js":
            "https://api.affirm.com/js/v2/affirm.js";
    }

    /**
     * Init Merchant data
     * Retrieve cancel and confirmation url settings
     */
    protected function initMerchant()
    {
        if (is_array($this->_options)) {
            $this->_options['merchant'] = array();
            $this->_options['merchant'] = [
                'user_confirmation_url' => $this->urlBuilder
                        ->getUrl('affirm/payment/confirm', ['_secure' => true]),
                'user_cancel_url' => $this->urlBuilder
                    ->getUrl('affirm/payment/cancel', ['_secure' => true])
            ];
        }
    }

    /**
     * Retrieve shipping address
     *
     * @return bool|\Magento\Quote\Model\Quote\Address
     */
    protected function getShippingAddress()
    {
        if ($this->quote->getId()) {
            return $this->quote->getShippingAddress();
        }
        return false;
    }

    /**
     * Retrieve all address data, this method is common for both billing and shipping address
     * The concrete type of address can be specified by 'type' parameter
     *
     * @param string $type
     */
    protected function initAddress($type = 'shipping')
    {
        if (is_array($this->_options)) {
            if ($type == 'shipping') {
                $address = $this
                    ->quote
                    ->getShippingAddress();
            } else {
                $address = $this
                    ->quote
                    ->getBillingAddress();
            }
            $firstName = $address
                ->getFirstname();
            $secondName = $address
                ->getLastname();
            if ($secondName) {
                $fullName = $firstName . " " . $secondName;
            } else {
                $fullName = $firstName;
            }
            $this->_options[$type] = [
                'name' => [
                    'full' => $fullName
                ]
            ];
            $this->configureAddress($address, $type);
        }
    }

    /**
     * Retrieve address information, this method is common for both billing and shipping address
     * The concrete type of address can be specified by second parameter
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param  string $type
     */
    protected function configureAddress(\Magento\Quote\Model\Quote\Address $address, $type)
    {
        if (is_array($this->_options)) {
            $this->_options[$type] = [
                'name' => [
                    'full' => $address->getFirstname() . ' ' . $address->getLastname()
                ],
                'address' => [
                    'line1'   => isset($address->getStreet()[1]) ? $address->getStreet()[0] . " "
                        . $address->getStreet()[1]: $address->getStreet()[0],
                    'city'    => $address->getCity(),
                    'state'   => $address->getRegionCode(),
                    'zipcode' => $address->getPostcode(),
                    'country' => $address->getCountryId()
                ]
            ];
        }
    }

    /**
     * Init config data
     * Depends on config mode it return appropriate information
     */
    protected function initConfig()
    {
        if (is_array($this->_options)) {
            $this->_options['config'] = array();
            $this->_options['config'] = [
                "financial_product_key" => $this->_scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox' ?
                    $this->_scopeConfig->getValue('payment/affirm_gateway/financial_product_key_sandbox'):
                    $this->_scopeConfig->getValue('payment/affirm_gateway/financial_product_key_production')
            ];
        }
    }

    /**
     * Init information about itemss
     * Specify
     */
    protected function initItems()
    {
        $items = $this->coreRegistry->registry('quote_items');
        $storedItems = [];
        if (is_array($this->_options)) {
            /** @var \Magento\Quote\Model\Quote\Item $item  */
            foreach ($items as $item) {
                $product = $item->getProduct();
                $imageUrl = $this->productHelper->getImageUrl($product);
                $storedItems[] = array (
                    'sku'            => $item->getSku(),
                    'display_name'   => $item->getName(),
                    'unit_price'     => $item->getPrice() * 100,
                    'qty'            => $item->getQty(),
                    "item_image_url" => $imageUrl,
                    'item_url'       => $item->getProduct()->getProductUrl(),
                );
            }
            $this->_options["items"] = $storedItems;
        }
    }

    /**
     * Init shipping data specify shipping method code
     */
    protected function initShipping()
    {
        $shipping = $this->quote->getShippingAddress()->getShippingMethod();
        if (is_array($this->_options)) {
            $this->_options['metadata'] = [
                'shipping_type' => $shipping
            ];
        }
    }
}
