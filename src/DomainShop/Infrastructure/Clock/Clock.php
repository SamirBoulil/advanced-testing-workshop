<?php

declare(strict_types=1);

namespace DomainShop\Infrastructure\Clock;

use DateTime;

class Clock implements \DomainShop\Infrastructure\Core\Clock
{
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDatetime(): \DateTimeInterface
    {
        $serverDatetime = getenv('SERVER_TIME');
        if ($serverDatetime) {
            return new Datetime(getenv('SERVER_TIME'));
        }

        return new Datetime('now');
    }
}
