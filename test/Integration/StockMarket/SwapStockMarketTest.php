<?php

declare(strict_types=1);

namespace Test\Integration\StockMarket;

use DomainShop\Infrastructure\StockMarket\SwapStockMarket;
use PHPUnit\Framework\TestCase;
use Swap\Builder;

class SwapStockMarketTest extends TestCase
{
    /**
     * @test
     */
    public function it_provides_the_exchange_rate()
    {
        $stockMarket = new SwapStockMarket(new Builder());
        $rate = $stockMarket->exchangeRate('EUR', 'USD', new \DateTime('2017-11-07 10:00:00'));
        $this->assertEquals(1.1562, $rate);
    }
}
