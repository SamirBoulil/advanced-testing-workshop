<?php

declare(strict_types=1);

namespace Test\Integration\Persistence;

use DomainShop\Entity\Order;

class InMemoryOrderRepository implements \DomainShop\Entity\OrderRepositoryInterface
{
    private $orders = [];

    public function byId(int $orderId): Order
    {
        return $this->orders[$orderId];
    }

    public function add(Order $order)
    {
        $this->orders[$order->id()] = $order;
    }
}
