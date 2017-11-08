<?php
declare(strict_types=1);

namespace DomainShop\Controller;

use Common\Persistence\Database;
use DomainShop\Entity\Order;
use DomainShop\Entity\Pricing;
use DomainShop\Service\ExchangeRateService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Swap\Builder;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewareInterface;

final class PayController implements MiddlewareInterface
{
    /**
     * @var ExchangeRateService
     */
    private $exchangeRateService;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(ExchangeRateService $exchangeRateService, RouterInterface $router, TemplateRendererInterface $renderer)
    {
        $this->exchangeRateService = $exchangeRateService;
        $this->router = $router;
        $this->renderer = $renderer;
    }

    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $orderId = $request->getAttribute('orderId');

        /** @var Order $order */
        $order = Database::retrieve(Order::class, (string)$orderId);

        /** @var Pricing $pricing */
        $pricing = Database::retrieve(Pricing::class, $order->getDomainNameExtension());

        if ($order->getPayInCurrency() !== $pricing->getCurrency()) {
            $exchangeRate = $this->exchangeRateService->getExchangeRate($pricing->getCurrency(), $order->getPayInCurrency());

            $currency = $order->getPayInCurrency();
            $amount = $pricing->getAmount() * $exchangeRate;
        } else {
            $currency = $pricing->getCurrency();
            $amount = $pricing->getAmount();
        }

        if ($request->getMethod() === 'POST') {
            $submittedData = $request->getParsedBody();
            if (isset($submittedData['pay'])) {
                $order->setWasPaid(true);
                Database::persist($order);
            }

            return new RedirectResponse(
                $this->router->generateUri('finish', ['orderId' => $orderId])
            );
        }

        $response->getBody()->write($this->renderer->render('pay.html.twig', [
            'orderId' => $orderId,
            'domainName' => $order->getDomainName(),
            'currency' => $currency,
            'amount' => $amount
        ]));

        return $response;
    }
}
