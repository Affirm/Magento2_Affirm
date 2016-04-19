<?php
namespace OnePica\Affirm\Helper;

use \Magento\Checkout\Model\Session;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Payment helper
 * The class is responsible
 * For get data from Gateway API
 * Facade class
 *
 * @package OnePica\Affirm\Helper
 */
class Payment
{
    /**
     * Affirm payment facade
     *
     * @var \Magento\Payment\Model\Method\Adapter
     */
    protected $payment;

    /**
     * Current checkout quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Method specification factory
     *
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * Init helper class
     *
     * @param \Magento\Payment\Model\Method\Adapter              $payment
     * @param Session                                            $session
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     */
    public function __construct(
        \Magento\Payment\Model\Method\Adapter $payment,
        Session $session,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->payment = $payment;
        $this->quote = $session->getQuote();
    }

    /**
     * Get payment method availability
     *
     * @return bool|mixed
     */
    public function isAffirmAvailable()
    {
        $check = $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable(
            $this->payment,
            $this->quote
        );
        if ($check) {
            return $this->payment->isAvailable($this->quote);
        }
        return false;
    }
}
