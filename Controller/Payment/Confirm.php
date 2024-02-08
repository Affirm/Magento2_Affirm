<?php

/**
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Astound\Affirm\Controller\Payment;


use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Astound\Affirm\Model\Checkout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;


/**
 * Class Confirm
 *
 * @package Astound\Affirm\Controller\Payment
 */
class Confirm implements CsrfAwareActionInterface
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * Quote management
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $quoteManagement;

    /**
     * Affirm checkout instance
     *
     * @var \Astound\Affirm\Model\Checkout
     */
    public $checkout;

    /**
     * Store sales quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    public $quote;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    public $resultFactory;

    /**
     * @var MessageManagerInterface
     */
    public $messageManager;

    /**
     * @var EventManagerInterface
     */
    public $_eventManager;

    /**
     * @var RequestInterface
     */
    public $request;



    /**
     * Inject objects to the Confirm action
     *
     * @param Context                 $context
     * @param CartManagementInterface $quoteManager
     * @param Session                 $checkoutSession
     * @param Checkout                $checkout
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        // Context $context,
        CartManagementInterface $quoteManager,
        Session $checkoutSession,
        Checkout $checkout,
        ResultFactory $resultFactory,
        MessageManagerInterface $messageManager,
        EventManagerInterface $_eventManager,
        RequestInterface $request
    ) {
        $this->checkout = $checkout;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManager;
        $this->quote = $checkoutSession->getQuote();
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->_eventManager = $_eventManager;
        $this->request = $request;
    }
 
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
 
    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }      

    /**
     * Dispatch request
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
    
        $token = $this->request->getPostValue('checkout_token');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
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
                return $resultRedirect->setPath('checkout/onepage/success');
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
                return $resultRedirect->setPath('checkout/cart');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t place the order.')
                );
                return $resultRedirect->setPath('checkout/cart');
            }
        }
    }
}
