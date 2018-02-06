<?php

declare(strict_types=1);

namespace Test\Integration\StockMarket;

use DomainShop\Infrastructure\Core\StockMarket;

class Fixed1156EchangeRate implements StockMarket
{
    public function exchangeRate(string $from, string $to, \DateTimeInterface $date): float
    {
        return 1.156;
    }
}
