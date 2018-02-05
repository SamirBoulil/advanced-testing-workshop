<?php

namespace DomainShop\Infrastructure\Core;

interface Clock
{
    public function getDatetime(): \DateTimeInterface;
}
