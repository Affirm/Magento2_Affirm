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

namespace Astound\Affirm\Controller\Payment\Extendable;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Model\Checkout;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Confirm
 *
 * @package Astound\Affirm\Controller\Payment\Extendable
 */
class Main extends Action
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Quote management
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * Affirm checkout instance
     *
     * @var \Astound\Affirm\Model\Checkout
     */
    protected $checkout;

    /**
     * Store sales quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Inject objects to the Confirm action
     *
     * @param Context                 $context
     * @param CartManagementInterface $quoteManager
     * @param Session                 $checkoutSession
     * @param Checkout                $checkout
     */
    public function __construct(
        Context $context,
        CartManagementInterface $quoteManager,
        Session $checkoutSession,
        Checkout $checkout
    ) {
        $this->checkout = $checkout;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManager;
        $this->quote = $checkoutSession->getQuote();
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $token = $this->getRequest()->getParam('checkout_token');
        if ($token) {
            try {
                $this->checkout->place($token);
                // prepare session to success or cancellation page
                $this->checkoutSession->clearHelperData();

                // "last successful quote"
                $quoteId = $this->quote->getId();
                $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

                // an order may be created
                $order = $this->checkout->getOrder();
                if ($order) {
                    $this->checkoutSession->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastOrderStatus($order->getStatus());
                }
                $this->_eventManager->dispatch(
                    'affirm_place_order_success',
                    ['order' => $order, 'quote' => $this->quote ]
                );
                $this->_redirect('checkout/onepage/success');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
                $this->_redirect('checkout/cart');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t place the order.')
                );
                $this->_redirect('checkout/cart');
            }
        }
    }
}
