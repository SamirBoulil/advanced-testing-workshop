<?php

namespace Test\Integration\StockMarket;

class Fixed1156EchangeRateTest extends \PHPUnit_Framework_TestCase
{
    public function testExchangeRate()
    {
        $this->assertEquals(
            1.156,
            (new Fixed1156EchangeRate())->exchangeRate('HELLO', 'WORLD', new \DateTime('now')
            )
        );
    }
}
