<?php

declare(strict_types=1);

namespace DomainShop\Application;

use Common\Persistence\Database;
use DomainShop\Entity\Order;
use DomainShop\Infrastructure\Core\Clock;
use DomainShop\Infrastructure\Core\StockMarket;
use DomainShop\Infrastructure\Persistence\PricingProvider;

class RegisterHandler
{
    /** @var StockMarket */
    private $stockMarket;

    /** @var Clock */
    private $clock;

    /** @var PricingProvider */
    private $priceProviderForDomain;

    public function __construct(StockMarket $stockMarket, Clock $clock, PricingProvider $priceProviderForDomain)
    {
        $this->stockMarket = $stockMarket;
        $this->clock = $clock;
        $this->priceProviderForDomain = $priceProviderForDomain;
    }

    public function handle(string $domainName, string $name, string $email, string $currency) : Order
    {
        $order = new Order();
        $order->setId(count(Database::retrieveAll(Order::class)) + 1);
        $order->setDomainName($domainName);
        $order->setOwnerName($name);
        $order->setOwnerEmailAddress($email);
        $order->setPayInCurrency($currency);

        $pricing = $this->priceProviderForDomain->getPricing($order->getDomainNameExtension());

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

        return $order;
    }
}
