<?php

declare(strict_types=1);

namespace Test\Integration\StockMarket;

use DomainShop\Infrastructure\Core\StockMarket;

class StockMarketStub implements StockMarket
{
    /** @var float */
    private $rate;

    public function setRate(float $rate)
    {
        $this->rate = $rate;
    }
    public function exchangeRate(string $from, string $to, \DateTimeInterface $date): float
    {
        return $this->rate;
    }
}
