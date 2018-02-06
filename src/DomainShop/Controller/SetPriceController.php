<?php
declare(strict_types=1);

namespace DomainShop\Controller;

use DomainShop\Application\SetPriceHandler;
use Zend\Stratigility\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SetPriceController implements MiddlewareInterface
{
    /**
     * @var SetPriceHandler
     */
    private $setPriceHandler;

    public function __construct(SetPriceHandler $setPriceHandler)
    {
        $this->setPriceHandler = $setPriceHandler;
    }

    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $submittedData = $request->getParsedBody();
        $this->setPriceHandler->handle($submittedData['extension'], $submittedData['currency'], (int)$submittedData['amount']);

        return $response;
    }
}
