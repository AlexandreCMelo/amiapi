<?php
declare(strict_types=1);

use Ams\Application\Handlers\HttpErrorHandler;
use Ams\Application\Handlers\ShutdownHandler;
use Ams\Application\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

ini_set("error_log",__DIR__ . '/../logs/app.log');

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__.'/../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
//$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');

$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);

/**
 *
 */
function dd()
{
    array_map(function($x) {
        var_dump($x);
    }, func_get_args());
    die;
}
