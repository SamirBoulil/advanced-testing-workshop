<?php

use DomainShop\Clock;
use DomainShop\Controller\CheckAvailabilityController;
use DomainShop\Controller\FinishController;
use DomainShop\Controller\HomepageController;
use DomainShop\Controller\PayController;
use DomainShop\Controller\RegisterController;
use DomainShop\Controller\SetPriceController;
use DomainShop\LockedClock;
use DomainShop\Resources\Views\TwigTemplates;
use DomainShop\Service\DomainAvailabilityService;
use DomainShop\Service\ExchangeRateService;
use DomainShop\Service\FakeExchangeRateService;
use DomainShop\Service\LiveExchangeRateService;
use DomainShop\Service\PayForOrder;
use DomainShop\Service\PricingService;
use DomainShop\Service\RegisterDomainName;
use DomainShop\Service\WhoisDomainAvailabilityService;
use DomainShop\SystemClock;
use Interop\Container\ContainerInterface;
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

$applicationEnv = getenv('ENV') ?: 'development';

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
            'onerror' => function (\Throwable $throwable) {
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
$container[NotFoundHandler::class] = function () {
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
 * Controllers
 */
$container[HomepageController::class] = function (ContainerInterface $container) {
    return new HomepageController($container->get(TemplateRendererInterface::class));
};
$container[CheckAvailabilityController::class] = function (ContainerInterface $container) {
    return new CheckAvailabilityController(
        $container->get(TemplateRendererInterface::class),
        $container->get(DomainAvailabilityService::class)
    );
};
$container[RegisterController::class] = function (ContainerInterface $container) {
    return new RegisterController(
        $container->get(RouterInterface::class),
        $container->get(TemplateRendererInterface::class),
        $container->get(RegisterDomainName::class)
    );
};
$container[PayController::class] = function (ContainerInterface $container) {
    return new PayController(
        $container->get(RouterInterface::class),
        $container->get(TemplateRendererInterface::class),
        $container->get(PricingService::class),
        $container->get(PayForOrder::class)
    );
};
$container[FinishController::class] = function (ContainerInterface $container) {
    return new FinishController(
        $container->get(TemplateRendererInterface::class)
    );
};
$container[SetPriceController::class] = function () {
    return new SetPriceController();
};

/*
 * Application services
 */
$container[RegisterDomainName::class] = function () {
    return new RegisterDomainName();
};
$container[PayForOrder::class] = function() {
    return new PayForOrder();
};
$container[PricingService::class] = function(ContainerInterface $container) {
    return new PricingService($container->get(ExchangeRateService::class));
};
$container[DomainAvailabilityService::class] = function() {
    return new WhoisDomainAvailabilityService();
};

if ($applicationEnv === 'testing') {
    $container[Clock::class] = function () {
        $serverTime = getenv('SERVER_TIME');
        if (!$serverTime) {
            throw new \RuntimeException('Undefined environment variable "SERVER_TIME"');
        }

        return new LockedClock(new \DateTimeImmutable($serverTime));
    };
} else {
    $container[Clock::class] = function () {
        return new SystemClock();
    };
}

if ($applicationEnv === 'testing') {
    $container[ExchangeRateService::class] = function () {
        return new FakeExchangeRateService();
    };
} else {
    $container[ExchangeRateService::class] = function (ContainerInterface $container) {
        return new LiveExchangeRateService($container->get(Clock::class));
    };
}

return $container;
