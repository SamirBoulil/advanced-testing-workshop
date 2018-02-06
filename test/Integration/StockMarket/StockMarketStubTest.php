<?php

namespace Test\Integration\StockMarket;

class StockMarketStubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_exchange_rate_it_has_been_initialized_with()
    {
        $stub = new StockMarketStub();
        $stub->setRate(1.1562);

        $this->assertEquals(1.1562, $stub->exchangeRate('HELLo', 'WORLD', new \DateTime('now')));
    }
}
