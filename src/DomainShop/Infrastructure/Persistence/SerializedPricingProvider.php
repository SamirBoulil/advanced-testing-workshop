<?php

namespace DomainShop\Infrastructure\Persistence;

use Common\Persistence\Database;
use DomainShop\Entity\Pricing;

class SerializedPricingProvider implements PricingProvider
{
    public function setPrice(string $extension, Pricing $pricing): void
    {
        Database::persist($pricing);
    }

    public function getPricing(string $extension): Pricing
    {
        return Database::retrieve(Pricing::class, $extension);
    }
}
