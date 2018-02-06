<?php

declare(strict_types=1);

namespace DomainShop\Application;

use Common\Persistence\Database;
use DomainShop\Entity\Pricing;
use DomainShop\Infrastructure\Persistence\PricingProvider;

class SetPriceHandler
{
    /** @var PricingProvider */
    private $pricingProvider;

    public function __construct(PricingProvider $pricingProvider)
    {
        $this->pricingProvider = $pricingProvider;
    }

    public function handle(string $extension, string $currency, int $amount)
    {
        try {
            $pricing = $this->pricingProvider->getPricing($extension);
        } catch (\RuntimeException $exception) {
            $pricing = new Pricing();
            $pricing->setExtension($extension);
        }

        $pricing->setCurrency($currency);
        $pricing->setAmount($amount);

        $this->pricingProvider->setPrice($extension, $pricing);
    }
}
