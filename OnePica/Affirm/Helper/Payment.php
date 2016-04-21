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
     * Customer session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $customerSession;

    /**
     * Init payment helper
     *
     * @param \Magento\Payment\Model\Method\Adapter              $payment
     * @param Session                                            $session
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Customer\Model\Session                    $customerSession
     */
    public function __construct(
        \Magento\Payment\Model\Method\Adapter $payment,
        Session $session,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->payment = $payment;
        $this->quote = $session->getQuote();
        $this->customerSession = $customerSession;
    }

    /**
     * Get payment method availability
     *
     * @return bool|mixed
     */
    public function isAffirmAvailable()
    {
        $checkData = [
            AbstractMethod::CHECK_USE_FOR_CURRENCY,
            AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ];

        $check = $this->methodSpecificationFactory
            ->create($checkData)
            ->isApplicable(
                $this->payment,
                $this->quote
            );
        if ($check) {
            return $this->payment->isAvailable($this->quote);
        }
        return false;
    }
}
