<?php

declare(strict_types=1);

namespace DomainShop\Infrastructure\Core;

interface StockMarket
{
    public function exchangeRate(string $from, string $to, \DateTimeInterface $dateTime): float;
}
