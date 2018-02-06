<?php

declare(strict_types=1);

namespace DomainShop\Infrastructure\Clock;

use DateTime;

class Clock implements \DomainShop\Infrastructure\Core\Clock
{
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime = null)
    {
        $this->dateTime = $dateTime;
    }

    public function getDatetime(): \DateTimeInterface
    {
        return $this->dateTime ?? new DateTime('now');
    }
}
