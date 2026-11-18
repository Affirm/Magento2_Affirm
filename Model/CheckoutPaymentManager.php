<?php

namespace Astound\Affirm\Model;

use Astound\Affirm\Api\CheckoutPaymentManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Class CheckoutPaymentManager
 *
 * @package Astound\Affirm\Model
 */
class CheckoutPaymentManager implements CheckoutPaymentManagerInterface
{
    /**
     * Checkout session object
     *
     * @var \Magento\Checkout\Model\Session
     */
    public $session;

    /**
     * Quote manager
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $quoteManager;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * Inject checkout session
     *
     * @param Session  $session
     * @param QuoteResource $quoteResource
     */
    public function __construct(Session $session, QuoteResource $quoteResource)
    {
        $this->session = $session;
        $this->quoteResource = $quoteResource;
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
            $data['method'] = \Astound\Affirm\Model\Ui\ConfigProvider::CODE;
            $payment->importData($data);
            $this->quoteResource->save($quote);
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
            if ($payment->getData('method') == \Astound\Affirm\Model\Ui\ConfigProvider::CODE) {
                //Clear data after verification
                $payment->setData('method', null);
                return true;
            }
        }
        return false;
    }
}
