<?php

declare(strict_types=1);

namespace DomainShop\Infrastructure\StockMarket;

use DomainShop\Infrastructure\Core\StockMarket;
use Swap\Builder;

class SwapStockMarket implements StockMarket
{
    /** @var Builder */
    private $swapBuilder;

    public function __construct(Builder $swapBuilder)
    {
        $this->swapBuilder = $swapBuilder;
    }

    public function exchangeRate(string $from, string $to, \DateTimeInterface $date): float
    {
        $swap = $this->swapBuilder->add('fixer')->build();

        return (float) $swap->historical($from . '/' . $to, $date)->getValue();
    }
}
