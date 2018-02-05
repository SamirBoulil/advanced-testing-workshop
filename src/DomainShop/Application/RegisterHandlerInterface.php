<?php

namespace DomainShop\Application;

use DomainShop\Entity\Order;

interface RegisterDomainNameHandlerInterface
{
    public function handle(Order $order) : void;
}
