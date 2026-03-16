<?php

declare(strict_types=1);

namespace Astound\Affirm\Service;

use Magento\Sales\Api\Data\OrderInterface;

class PlacedOrderHolder
{
    private ?OrderInterface $order = null;

    public function hold(OrderInterface $order): void
    {
        if ($this->order) {
            throw new \DomainException('Order is already held');
        }

        $this->order = $order;
    }

    public function retrieve(): ?OrderInterface
    {
        return $this->order;
    }
}
