<?php

declare(strict_types=1);

namespace DomainShop\Application;

use DomainShop\Entity\Order;
use DomainShop\Entity\OrderRepositoryInterface;

class PayHandler
{
    /**
     * @var \DomainShop\Entity\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(Order $order)
    {
        $order->setWasPaid(true);
        $this->orderRepository->add($order);

        return $order;
    }
}
