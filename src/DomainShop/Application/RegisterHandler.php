<?php

declare(strict_types=1);

namespace DomainShop\Application;

use Common\Persistence\Database;
use DomainShop\Entity\Order;
use DomainShop\Entity\Pricing;
use DomainShop\Infrastructure\Core\Clock;
use DomainShop\Infrastructure\Core\StockMarket;

class RegisterDomainHandlerHandler implements RegisterDomainNameHandlerInterface
{
    /** @var StockMarket */
    private $stockMarket;

    /** @var Clock */
    private $clock;

    public function __construct(StockMarket $stockMarket, Clock $clock)
    {
        $this->stockMarket = $stockMarket;
        $this->clock = $clock;
    }

    public function handle(Order $order) : void {
        $pricing = Database::retrieve(Pricing::class, $order->getDomainNameExtension());

        if ($order->getPayInCurrency() !== $pricing->getCurrency()) {
            $rate = $this->stockMarket->exchangeRate(
                $pricing->getCurrency(),
                $order->getPayInCurrency(),
                $this->clock->getDatetime()
            );
            $amount = (int) round($pricing->getAmount() * $rate);
        } else {
            $amount = $pricing->getAmount();
        }

        $order->setAmount($amount);

        Database::persist($order);
    }
}
