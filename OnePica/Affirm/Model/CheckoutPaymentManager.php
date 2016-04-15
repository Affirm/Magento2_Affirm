<?php

namespace OnePica\Affirm\Model;

use OnePica\Affirm\Api\CheckoutPaymentManagerInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Quote\Api\CartManagementInterface;
/**
 * Class CheckoutPaymentManager
 *
 * @package OnePica\Affirm\Model
 */
class CheckoutPaymentManager implements CheckoutPaymentManagerInterface
{
    /**
     * Checkout session object
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Quote manager
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $quoteManager;

    /**
     * Inject checkout session
     *
     * @param Session  $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Init payment
     *
     * @return bool|string
     */
    public function initPayment()
    {
        $quote = $this->session->getQuote();
        if ($quote->getId()) {
            $payment = $quote->getPayment();
            $data['method'] = \OnePica\Affirm\Model\Ui\ConfigProvider::CODE;
            $payment->importData($data);
            $quote->save();
            return true;
        }
        return false;
    }

    /**
     * Verify affirm selection
     *
     * @return bool|mixed
     */
    public function verifyAffirm()
    {
        $quote = $this->session->getQuote();
        if ($quote->getId()) {
            $payment = $quote->getPayment();
            if ($payment->getData('method') == \OnePica\Affirm\Model\Ui\ConfigProvider::CODE) {
                return true;
            }
        }
        return false;
    }
}
