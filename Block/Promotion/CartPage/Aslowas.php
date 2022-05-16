<?php

namespace Astound\Affirm\Block\Promotion\CartPage;

use Astound\Affirm\Block\Promotion\AslowasAbstract;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Helper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class AsLowAs
 *
 * @package Astound\Affirm\Block\Promotion\CartPage
 */
class Aslowas extends AslowasAbstract
{
    /**
     * Data which should be converted to json from the Block data.
     *
     * @var array
     */
    protected $data = ['logo', 'script', 'public_api_key', 'min_order_total', 'max_order_total', 'element_id'];

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Financing program helper factory
     *
     * @var Helper\FinancingProgram
     */
    protected $fpHelper;

    /**
     * Cart page block.
     *
     * @param Template\Context               $context
     * @param ConfigProvider                 $configProvider
     * @param \Astound\Affirm\Model\Config   $configAffirm
     * @param \Astound\Affirm\Helper\Payment $helperAffirm
     * @param Session                        $session
     * @param array                          $data
     * @param Helper\AsLowAs                 $asLowAs
     * @param \Astound\Affirm\Helper\Rule    $rule
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        \Astound\Affirm\Model\Config $configAffirm,
        \Astound\Affirm\Helper\Payment $helperAffirm,
        Session $session,
        Helper\AsLowAs $asLowAsHelper,
        \Astound\Affirm\Helper\Rule $rule,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->checkoutSession = $session;
        parent::__construct($context, $configProvider, $configAffirm, $helperAffirm, $asLowAsHelper, $rule, $categoryCollectionFactory, $data);
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Validate block before showing on front in checkout cart
     * There can be added new validators by needs.
     *
     * @return boolean
     */
    public function validate()
    {
        if ($this->getQuote()) {
            // Payment availability flag
            $isAvailableFlag = $this->getPaymentConfigValue('active');

            //Validate aslowas block based on appropriate values and conditions
            if ($isAvailableFlag && $this->affirmPaymentHelper->isAffirmAvailable()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add selector data to the block context.
     * This needs for bundle product, because bundle has
     * different structure.
     */
    public function process()
    {
        $this->setData('element_id', 'als_pcc');

        parent::process();
    }

    /**
     * get MFP value for current cart
     * @return string
     */
    public function getMFPValue()
    {
        return $this->asLowAsHelper->getFinancingProgramValue();
    }

    public function getLearnMoreValue(){
        return $this->asLowAsHelper->isVisibleLearnmore() ? 'true' :'false';
    }
}
