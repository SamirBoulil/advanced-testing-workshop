<?php
declare(strict_types=1);

namespace DomainShop\Controller;

use Common\Persistence\Database;
use DomainShop\Application\PayHandler;
use DomainShop\Entity\Order;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewareInterface;

final class PayController implements MiddlewareInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /** @var PayHandler */
    private $payHandler;

    /**
     * PayController constructor.
     *
     * @param RouterInterface           $router
     * @param TemplateRendererInterface $renderer
     * @param PayHandler                $payHandler
     */
    public function __construct(RouterInterface $router, TemplateRendererInterface $renderer, PayHandler $payHandler)
    {
        $this->router = $router;
        $this->renderer = $renderer;
        $this->payHandler = $payHandler;
    }

    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $orderId = $request->getAttribute('orderId');

        $order = Database::retrieve(Order::class, (string)$orderId);

        if ($request->getMethod() === 'POST') {
            $submittedData = $request->getParsedBody();
            if (isset($submittedData['pay'])) {
                $this->payHandler->handle($order);
            }

            return new RedirectResponse(
                $this->router->generateUri('finish', ['orderId' => $orderId])
            );
        }

        $response->getBody()->write($this->renderer->render('pay.html.twig', [
            'orderId' => $orderId,
            'domainName' => $order->getDomainName(),
            'currency' => $order->getPayInCurrency(),
            'amount' => $order->getAmount()
        ]));

        return $response;
    }
}
