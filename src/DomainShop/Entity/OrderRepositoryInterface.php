<?php

namespace DomainShop\Entity;

interface OrderRepositoryInterface
{
    public function byId(int $orderId): Order;
    public function add(Order $order);
}
