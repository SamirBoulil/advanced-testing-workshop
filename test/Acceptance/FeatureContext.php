<?php

namespace Test\Acceptance;

use Behat\Behat\Context\Context;
use DomainShop\Application\PayHandler;
use DomainShop\Application\RegisterHandler;
use DomainShop\Entity\Order;
use DomainShop\Entity\OrderRepositoryInterface;
use DomainShop\Entity\Pricing;
use DomainShop\Infrastructure\Clock\Clock;
use DomainShop\Infrastructure\Core\StockMarket;
use DomainShop\Infrastructure\Persistence\PricingProvider;
use DomainShop\Infrastructure\Persistence\PricingProviderStub;
use Test\Integration\Persistence\InMemoryOrderRepository;
use Test\Integration\StockMarket\StockMarketStub;

final class FeatureContext implements Context
{
    /** @var \DomainShop\Entity\OrderRepositoryInterface */
    protected $orderRepository;

    /** @var PricingProvider */
    protected $priceProviderForDomain;

    /** @var PayHandler */
    private $payHandler;

    /** @var StockMarket */
    private $stockMarket;

    /** @var Order */
    private $order;

    /** @var RegisterHandler  */
    private $registerHandler;

    public function __construct()
    {
        $this->stockMarket = new StockMarketStub();
        $this->priceProviderForDomain = new PricingProviderStub();
        $this->registerHandler = new RegisterHandler(
            $this->stockMarket,
            new Clock(new \Datetime('now')),
            $this->priceProviderForDomain
        );
        $this->orderRepository = new InMemoryOrderRepository();
        $this->payHandler = new PayHandler($this->orderRepository);
    }

    /**
     * @Given /^I register "([^"]*)" to "([^"]*)" with email address "([^"]*)" and I want to pay in USD$/
     */
    public function iRegisterToWithEmailAddressAndIWantToPayInUSD($domainName, $name, $email)
    {
        $this->order = $this->registerHandler->handle($domainName, $name, $email, 'USD');
    }

    /**
     * @Given /^I pay (\d+)\.(\d+) USD for it$/
     */
    public function iPayUSDForIt($price, $decimals)
    {
        $this->payHandler->handle($this->order);
    }

    /**
     * @Then /^the order was paid$/
     */
    public function theOrderWasPaid()
    {
        if (null === $this->order && $this->order->wasPaid()) {
            throw new \LogicException('Order not found in DB');
        }
    }

    /**
     * @Given /^a \.com domain name costs EUR (\d+)\.(\d+)$/
     */
    public function aComDomainNameCostsEUR($arg1, $arg2)
    {
        $pricing = new Pricing();
        $pricing->setExtension('.com');
        $pricing->setCurrency('EUR');
        $pricing->setAmount((float) $arg1 . '.' . $arg2);

        $this->priceProviderForDomain->setPrice('.com', $pricing);
    }

    /**
     * @Given /^the exchange rate EUR to USD is (\d+)\.(\d+)$/
     */
    public function theExchangeRateEURToUSDIs($integer, $decimals)
    {
        $this->stockMarket->setRate((float) $integer . '.' . $decimals);
    }
}
