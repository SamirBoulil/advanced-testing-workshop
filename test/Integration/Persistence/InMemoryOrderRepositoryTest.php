<?php

namespace Test\Integration\Persistence;

use DomainShop\Entity\Order;

class InMemoryOrderRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $inMemoryOrderRepository;

    public function setUp()
    {
        parent::setUp();
        $this->inMemoryOrderRepository = new InMemoryOrderRepository();
    }

    /**
     * @test
     */
    public function it_adds_and_returns_the_orders()
    {
        $order = new Order();
        $order->setId(1);
        $this->inMemoryOrderRepository->add($order);
        $this->assertSame($order, $this->inMemoryOrderRepository->byId(1));
    }
}
