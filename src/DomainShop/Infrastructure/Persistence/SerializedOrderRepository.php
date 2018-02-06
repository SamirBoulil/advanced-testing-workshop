<?php

namespace DomainShop\Infrastructure\Persistence;

use Common\Persistence\Database;
use DomainShop\Entity\Order;
use DomainShop\Entity\OrderRepositoryInterface;

class SerializedOrderRepository implements OrderRepositoryInterface
{
    public function byId(int $orderId): Order
    {
        return Database::retrieve((string) $orderId);
    }

    public function add(Order $order)
    {
        Database::persist($order);
    }
}
