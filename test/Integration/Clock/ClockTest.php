<?php

declare(strict_types=1);

namespace Test\Integration\Clock;

use DateTime;
use DomainShop\Infrastructure\Clock\Clock;
use PHPUnit\Framework\TestCase;

class ClockTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_time_it_has_been_initialized_with()
    {
        $aDate = new \DateTime('2017-11-07 10:00:00');
        $clock = new Clock($aDate);

        $this->assertEquals($aDate, $clock->getDatetime());
    }

    /**
     * @test
     */
    public function it_returns_the_current_time_if_it_has_not_been_initialized_with_a_date()
    {
        $clock = new Clock();
        $this->assertInstanceOf(DateTime::class, $clock->getDatetime());
    }
}
