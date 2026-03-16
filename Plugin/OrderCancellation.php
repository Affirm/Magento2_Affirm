<?php

declare(strict_types=1);

namespace Astound\Affirm\Plugin;

use Astound\Affirm\Service\PlacedOrderHolder;

use Closure;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use RuntimeException;

class OrderCancellation
{
    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository 
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository 
     * @param \Astound\Affirm\Service\PlacedOrderHolder\PlacedOrderHolder $placedOrderHolder 
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory 
     */
    public function __construct(
        private CartRepositoryInterface $quoteRepository,
        private OrderRepositoryInterface $orderRepository,
        private PlacedOrderHolder $placedOrderHolder,
        private CreditmemoFactory $creditmemoFactory
    ) {
    }

    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        Closure $proceed,
        int $cartId,
        PaymentInterface $payment = null
    ): int {
        try {
            return (int)$proceed($cartId, $payment);
        } catch (\Throwable $e) {
            $quote = $this->quoteRepository->get((int) $cartId);

            $payment = $quote->getPayment();

            // Abort if the payment method is not relevant.
            if ($payment->getMethod() !== 'affirm_gateway') {
                throw $e;
            }

            $errorMessagePrefix = 'Unable to cancel payment: ';

            /** @var \Magento\Sales\Model\Order|null */
            $order = $this->placedOrderHolder->retrieve();

            // Abort if the order object is not available
            if (!$order) {
                throw new RuntimeException(
                    $errorMessagePrefix . "Order data unavailable. Reserved order ID: {$quote->getReservedOrderId()}",
                    $e->getCode(),
                    $e
                );
            }

            // Abort if the order object is not relevant for transaction.
            if ($order->getIncrementId() !== $quote->getReservedOrderId()) {
                throw new RuntimeException(
                    $errorMessagePrefix . "Available order data ({$order->getIncrementId()}, {$order->getId()}) doesn't match the quote value: {$quote->getReservedOrderId()}",
                    $e->getCode(),
                    $e
                );
            }

            // Cancel the order in case when it was saved.
            if ($order->getId()) {
                $order->cancel();

                $this->orderRepository->save($order);

                throw $e;
            }

            /** @var \Magento\Sales\Model\Order\Payment|null */
            $orderPayment = $order->getPayment();

            // Abort if the order lacks payment information.
            if (!$orderPayment) {
                throw $e;
            }

            $methodInstance = $orderPayment->getMethodInstance();
            $methodInstance->setStore($order->getStoreId());

            $errorMessage = "";

            if (!$methodInstance->canRefund()) {
                $errorMessage = "Transaction can not be refunded.";
            }

            if (!$orderPayment->getCreatedTransaction()) {
                $errorMessage = "Transaction information is missing.";
            }

            if (!$orderPayment->getCreatedInvoice()) {
                $errorMessage = "Invoice is missing.";
            }

            if ($errorMessage) {
                throw new RuntimeException(
                    $errorMessagePrefix . $errorMessage,
                    $e->getCode(),
                    $e
                );
            }

            $creditmemo = $this->creditmemoFactory->createByOrder($order);
            $creditmemo->setInvoice($orderPayment->getCreatedInvoice());

            $orderPayment->setCreditmemo($creditmemo);
            $orderPayment->setParentTransactionId($orderPayment->getCreatedTransaction()->getTxnId());

            $methodInstance->refund($orderPayment, $orderPayment->getAmountPaid());

            throw $e;
        }
    }
}
