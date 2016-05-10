<?php
/**
 *
 */
namespace Astound\Affirm\Block\Promotion;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Astound\Affirm\Model\Config;

/**
 * Class Banner
 *
 * @package Astound\Affirm\Block\Promotion
 */
class Banners extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $section;

    /**
     * @var string
     */
    protected $position;

    /**
     * Config payment
     *
     * @var \Astound\Affirm\Model\Config
     */
    protected $affirmPaymentConfig;

    /**
     * Inject all needed objects
     *
     * @param Template\Context $context
     * @param Config           $configAffirm
     * @param ConfigProvider   $configProvider
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        \Astound\Affirm\Model\Config $configAffirm,
        \Astound\Affirm\Model\Ui\ConfigProvider $configProvider,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->affirmPaymentConfig = $configAffirm;
        $this->position = isset($data['position']) ? $data['position']: '';
        $this->section = isset($data['section']) ? $data['section']: 0;
        $this->configProvider = $configProvider;
    }

    /**
     * Get promo key from
     *
     * @return mixed
     */
    protected function getPublisherId()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_promo/promo_key');
    }

    /**
     * Get is promo active
     *
     * @return mixed
     */
    protected function getIsActive()
    {
        return $this->_scopeConfig->getValue('affirm/affirm_promo/enabled');
    }

    /**
     * Verify block before render html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getIsActive()) {
            return '';
        }
        $display  = $this->affirmPaymentConfig->getBmlDisplay($this->section);
        $position = $this->affirmPaymentConfig->getBmlPosition($this->section);

        if (!$display) {
            return '';
        }
        if ($this->position != $position) {
            return '';
        }
        $this->setPromoKey($this->affirmPaymentConfig->getPromoKey());
        $this->setSize($this->affirmPaymentConfig->getBmlSize($this->section));
        return parent::_toHtml();
    }

    /**
     * Get options for
     *
     * @return string
     */
    public function getOptions()
    {
        $options = [];
        $configProvider = $this->configProvider->getConfig();
        if ($configProvider['payment'][ConfigProvider::CODE]) {
            $config = $configProvider['payment'][ConfigProvider::CODE];
            if ($config && isset($config['script']) && isset($config['apiKeyPublic'])) {
                $options['script'] = $config['script'];
                $options['public_api_key'] = $config['apiKeyPublic'];
            }
        }
        return json_encode($options);
    }
}
