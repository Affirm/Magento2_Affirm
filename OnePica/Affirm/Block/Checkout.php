<?php
namespace OnePica\Affirm\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
/**
 * Class Checkout
 *
 * @package OnePica\Affirm\Block
 */
class Checkout extends \Magento\Framework\View\Element\Template
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
     * Inject objects in the block
     *
     * @param Template\Context            $context
     * @param \Magento\Framework\Registry $registry
     * @param UrlInterface                $urlInterface
     * @param array                       $data
     */
    public function __construct(Template\Context $context,
        \Magento\Framework\Registry $registry,
        UrlInterface $urlInterface,
        array $data = []
    )
    {
        $this->urlBuilder = $urlInterface;
        $this->coreRegistry = $registry;
        $this->quote = $this->coreRegistry->registry('current_quote');
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
        $this->initConfig();
        $this->initMerchant();
        $this->initAddress();
        $this->initAddress('billing');
        $this->initItems();
        $this->_options['order_id'] = $orderId;
        return json_encode($this->_options);
    }

    /**
     * Init Merchant data
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
     * Init shipping
     */
    protected function initAddress($type = 'shipping')
    {
        if (is_array($this->_options)) {
            if ($type == 'shipping') {
                $address = $this
                    ->quote
                    ->getShippingAddress();
            } else if ($type == 'billing') {
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
     * Configure address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param  string $type
     */
    protected function configureAddress(\Magento\Quote\Model\Quote\Address $address, $type)
    {
        if (is_array($this->_options)) {
            $this->_options[$type] = [
                'address' => [
                    'line1'   => $address->getStreet()[0],
                    'city'    => $address->getCity(),
                    'state'   => $address->getRegionCode(),
                    'zipcode' => $address->getPostcode(),
                    'country' => $address->getCountryId()
                ]
            ];
        }
    }

    /**
     * Init Config Data
     */
    protected function initConfig()
    {
        if (is_array($this->_options)) {
            $this->_options['config'] = array();
            $this->_options['config'] = [
                "financial_product_key"
                => $this->_scopeConfig->getValue('payment/affirm_gateway/financial_product_key_sandbox')
            ];
        }
    }

    /**
     * Init items
     */
    public function initItems()
    {
        $items = $this->quote->getItems();
        if (is_array($this->_options)) {
            $this->_options['items'] = [];
            /** @var \Magento\Quote\Model\Quote\Item $item  */
            foreach ($items as $item) {
                $this->_options['items'][] = [
                    'display_name'   => $item->getName(),
                    'sku'            => $item->getSku(),
                    'unit_price'     => $item->getPrice() * 100,
                    'qty'            => $item->getQty(),
                    'item_image_url' => $item->getProduct()->getProductUrl(),
                    'item_url'       => $item->getProduct()->getRequestPath()
                ];
            }
        }
    }
}
