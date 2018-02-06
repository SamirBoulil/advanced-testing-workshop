<?php

use DomainShop\Application\PayHandler;
use DomainShop\Application\RegisterHandler;
use DomainShop\Application\SetPriceHandler;
use DomainShop\Controller\CheckAvailabilityController;
use DomainShop\Controller\FinishController;
use DomainShop\Controller\HomepageController;
use DomainShop\Controller\PayController;
use DomainShop\Controller\RegisterController;
use DomainShop\Controller\SetPriceController;
use DomainShop\Entity\OrderRepositoryInterface;
use DomainShop\Infrastructure\Clock\Clock;
use DomainShop\Infrastructure\Core\Clock as ClockInterface;
use DomainShop\Infrastructure\Core\StockMarket;
use DomainShop\Infrastructure\Persistence\PricingProvider;
use DomainShop\Infrastructure\Persistence\SerializedOrderRepository;
use DomainShop\Infrastructure\Persistence\SerializedPricingProvider;
use DomainShop\Infrastructure\StockMarket\SwapStockMarket;
use DomainShop\Resources\Views\TwigTemplates;
use Interop\Container\ContainerInterface;
use Swap\Builder;
use Symfony\Component\Debug\Debug;
use Xtreamwayz\Pimple\Container;
use Zend\Diactoros\Response;
use Zend\Expressive\Application;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRendererFactory;
use Zend\Stratigility\Middleware\NotFoundHandler;

Debug::enable();

$container = new Container();

$applicationEnv = getenv('APPLICATION_ENV') ?: 'development';
$container['config'] = [
    'middleware_pipeline' => [
        'routing' => [
            'middleware' => array(
                ApplicationFactory::ROUTING_MIDDLEWARE,
                ApplicationFactory::DISPATCH_MIDDLEWARE,
            ),
            'priority' => 1,
        ],
        [
            'middleware' => NotFoundHandler::class,
            'priority' => -1,
        ],
    ],
    'debug' => $applicationEnv !== 'production',
    'final_handler' => [
        'options' => [
            'env' => $applicationEnv,
            'onerror' => function(\Throwable $throwable) {
                error_log((string)$throwable);
            }
        ]
    ],
    'templates' => [
        'extension' => 'html.twig',
        'paths' => [
            TwigTemplates::getPath()
        ]
    ],
    'twig' => [
        'globals' => [
            'applicationEnv' => $applicationEnv
        ]
    ],
    'routes' => [
        [
            'name' => 'homepage',
            'path' => '/',
            'middleware' => HomepageController::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'check_availability',
            'path' => '/check-availability',
            'middleware' => CheckAvailabilityController::class,
            'allowed_methods' => ['POST']
        ],
        [
            'name' => 'register',
            'path' => '/register',
            'middleware' => RegisterController::class,
            'allowed_methods' => ['POST']
        ],
        [
            'name' => 'pay',
            'path' => '/pay/{orderId}',
            'middleware' => PayController::class,
            'allowed_methods' => ['GET', 'POST']
        ],
        [
            'name' => 'finish',
            'path' => '/finish/{orderId}',
            'middleware' => FinishController::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'set_price',
            'path' => '/set-price',
            'middleware' => SetPriceController::class,
            'allowed_methods' => ['POST']
        ],
    ]
];

/*
 * Zend Expressive Application
 */
$container[RouterInterface::class] = function () {
    return new FastRouteRouter();
};
$container[Application::class] = new ApplicationFactory();
$container[NotFoundHandler::class] = function() {
    return new NotFoundHandler(new Response());
};

/*
 * Templating
 */
$container[TemplateRendererInterface::class] = new TwigRendererFactory();
$container[ServerUrlHelper::class] = function () {
    return new ServerUrlHelper();
};
$container[UrlHelper::class] = function (ContainerInterface $container) {
    return new UrlHelper($container[RouterInterface::class]);
};

/*
 * Infrastructure
 */
$container[OrderRepositoryInterface::class] = function (ContainerInterface $container) {
    return new SerializedOrderRepository();
};
$container[PricingProvider::class] = function (ContainerInterface $container) {
    return new SerializedPricingProvider();
};

/*
 * Application
 */
$container[RegisterHandler::class] = function (ContainerInterface $container) {
    return new RegisterHandler(
        $container->get(StockMarket::class),
        $container->get(ClockInterface::class),
        $container->get(PricingProvider::class)
    );
};
$container[PayHandler::class] = function (ContainerInterface $container) {
    return new PayHandler($container->get(OrderRepositoryInterface::class));
};
$container[SetPriceHandler::class] = function (ContainerInterface $container) {
    return new SetPriceHandler($container->get(PricingProvider::class));
};

/*
 * Controllers
 */
$container[HomepageController::class] = function (ContainerInterface $container) {
    return new HomepageController($container->get(TemplateRendererInterface::class));
};
$container[CheckAvailabilityController::class] = function (ContainerInterface $container) {
    return new CheckAvailabilityController($container->get(TemplateRendererInterface::class));
};
$container[RegisterController::class] = function (ContainerInterface $container) {
    return new RegisterController(
        $container->get(RouterInterface::class),
        $container->get(TemplateRendererInterface::class),
        $container->get(RegisterHandler::class)
    );
};
$container[PayController::class] = function (ContainerInterface $container) {
    return new PayController(
        $container->get(RouterInterface::class),
        $container->get(TemplateRendererInterface::class),
        $container->get(PayHandler::class)
    );
};
$container[FinishController::class] = function (ContainerInterface $container) {
    return new FinishController(
        $container->get(TemplateRendererInterface::class)
    );
};
$container[SetPriceController::class] = function (ContainerInterface $container) {
    return new SetPriceController($container->get(SetPriceHandler::class));
};

$container[ClockInterface::class] = function () {
    if ('testing' === getenv('APPLICATION_ENV')) {
        return new Clock(new Datetime(getenv('SERVER_TIME')));
    }

    return new Clock(new Datetime('now'));
};

$container[StockMarket::class] = function () {
//    if ('testing' === getenv('APPLICATION_ENV')) {
//        return new Fixed1156EchangeRate();
//    }

    return new SwapStockMarket(new Builder());
};

return $container;
