<?php

namespace DomainShop\Infrastructure\Persistence;

use DomainShop\Entity\Pricing;

interface PricingProvider
{
    public function setPrice(string $extension, Pricing $pricing): void;
    public function getPricing(string $extension): Pricing;
}
