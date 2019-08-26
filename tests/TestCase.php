<?php
declare(strict_types=1);

namespace Tests;

use Buzz\Client\Curl as Psr18HttpClient;
use Buzz\Client\MultiCurl as Psr18HttpMultiClient;
use DI\ContainerBuilder;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;

class TestCase extends PHPUnit_TestCase
{

    const HTTP_SUCCESS_CODE = 200;
    const HTTP_NOTE_VALID_CODE = 400;
    const HTTP_NOT_FOUND = 404;
    /**
     * @var Psr17Factory
     */
    protected $requestFactory = null;

    /**
     * @var Psr18HttpClient
     */
    protected $httpClient = null;

    /**
     * @var Psr18HttpMultiClient
     */
    protected $httpMultiClient = null;

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = include __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        // Set up dependencies
        $dependencies = include __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();

        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register routes
        $routes = include __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function createRequest(
        string $method,
        string $path
    ): ResponseInterface {
        $requestFactory = $this->getRequestFactory();

        $request = $requestFactory->createRequest(
            $method,
            $path
        );


        $response = $this->getHttpClient($requestFactory)->sendRequest($request);

        return $response;
    }


    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpClient
     */
    public function getHttpClient(Psr17Factory $psr17Factory): Psr18HttpClient
    {
        return $this->httpClient ?? (new Psr18HttpClient($psr17Factory));
    }

    /**
     * @return Psr17Factory
     */
    public function getRequestFactory(): Psr17Factory
    {
        return $this->requestFactory ?? (new Psr17Factory);
    }

    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpMultiClient
     */
    public function getHttpMultiClient(Psr17Factory $psr17Factory): Psr18HttpMultiClient
    {
        return $this->httpMultiClient ?? (new Psr18HttpMultiClient($psr17Factory));
    }

    /**
     * @param $message
     */
    public function writeMessage(?string $message)
    {
        fwrite(STDERR, print_r($message.PHP_EOL, true));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function url()
    {

        $settings = $this->getAppInstance()->getContainer()->get('settings');
        $url = $settings['isDevEnviroment'] ? "http://" .$_SERVER['HTTP_HOST'] : $settings['productionUri'];
        return $url;
    }
}
