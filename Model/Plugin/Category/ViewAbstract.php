<?php
namespace Astound\Affirm\Model\Plugin\Category;

use Astound\Affirm\Model\Config as Config;
use Astound\Affirm\Helper\AsLowAs;
use Magento\Store\Model\StoreManagerInterface;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class ViewAbstract
 *
 * @package Astound\Affirm\Model\Plugin\Category
 */
class ViewAbstract extends \Magento\Framework\DataObject
{
    /**
     * Data which should be converted to json from data.
     *
     * @var array
     */
    protected $data = ['logo', 'script', 'public_api_key'];

    /**
     * Colors which could be set in "data-affirm-color".
     *
     * @var array
     */
    protected $dataColors = ['blue', 'black', 'white'];

    /**
     * Affirm Min Mpp
     *
     * @var mixed
     */
    protected $minMPP = null;

    /**
     * Affirm config
     *
     * @var Config
     */
    protected $config;

    /**
     * AsLowAs helper
     *
     * @var Config
     */
    protected $asLowAsHelper;

    /**
     * Affirm config model payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * ProductList constructor.
     *
     * @param StoreManagerInterface         $storeManagerInterface
     * @param ConfigProvider                $configProvider
     * @param Config                        $configAffirm
     * @param AsLowAs                       $asLowAs
     * @param ProductCollectionFactory $productCollectionFactory
     *
     */
    public function __construct(
            StoreManagerInterface $storeManagerInterface,
            ConfigProvider $configProvider,
            Config $configAffirm,
            AsLowAs $asLowAs,
            ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;

        $this->asLowAsHelper = $asLowAs;
        $this->configProvider = $configProvider;

        $currentWebsiteId = $storeManagerInterface->getStore()->getWebsiteId();
        $this->affirmPaymentConfig = $configAffirm;
        $this->affirmPaymentConfig->setWebsiteId($currentWebsiteId);

        if ($this->affirmPaymentConfig->getAsLowAsLogo()) {
            $this->setData('logo', $this->affirmPaymentConfig->getAsLowAsLogo());

            $configProvider = $this->configProvider->getConfig();
            if ($configProvider['payment'][ConfigProvider::CODE]) {
                $config = $configProvider['payment'][ConfigProvider::CODE];
                $this->setData('script', $config['script']);
                $this->setData('public_api_key', $config['apiKeyPublic']);
            }
            // Set max and min options amounts from payment configuration
            $this->setData('min_order_total', $this->getPaymentConfigValue('min_order_total'));
            $this->setData('max_order_total', $this->getPaymentConfigValue('max_order_total'));
        }
    }

    /**
     * Get specified data about As Low AS
     * and convert it
     * to json format.
     *
     * @return string
     */
    public function getWidgetData()
    {
        if ($this->data && $this->affirmPaymentConfig->getAsLowAsLogo() &&
            $this->affirmPaymentConfig->getAsLowAsMonths()) {
            return $this->convertToJson($this->data);
        }
        return '';
    }

    /**
     * Get data-attribute for affirm logo color
     *
     * @return string
     */
    public function getDataAffirmColor()
    {
        if(in_array($this->getData('logo'), $this->dataColors)) {
            return 'data-affirm-color="' . $this->getData('logo')  . '"';
        }
        return '';
    }

    /**
     * Get is defined value from configuration
     *
     * @param string $value
     * @return bool|mixed
     */
    public function getPaymentConfigValue($value)
    {
        return $this->affirmPaymentConfig->getConfigData($value) ?
            $this->affirmPaymentConfig->getConfigData($value): false;
    }

    /**Get Min Mpp
     *
     * @return float|int
     */
    protected function getMinMPP()
    {
        if ($this->minMPP == null) {
            $this->minMPP = $this->affirmPaymentConfig->getAsLowAsMinMpp();
            if (empty($this->minMPP)) {
                $this->minMPP = 0;
            }
        }

        return $this->minMPP;
    }
}
