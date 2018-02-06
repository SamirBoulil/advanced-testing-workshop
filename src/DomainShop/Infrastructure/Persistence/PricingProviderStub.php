<?php

declare(strict_types=1);

namespace DomainShop\Infrastructure\Persistence;

use DomainShop\Entity\Pricing;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricingProviderStub implements PricingProvider
{
    private $prices = [];

    public function setPrice(string $extension, Pricing $pricing): void
    {
        $this->prices[$extension] = $pricing;
    }

    public function getPricing(string $extension): Pricing
    {
        return $this->prices[$extension];
    }
}
