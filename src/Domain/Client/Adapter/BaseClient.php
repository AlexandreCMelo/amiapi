<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Adapter;

use Buzz\Client\Curl as Psr18HttpClient;
use Buzz\Client\MultiCurl as Psr18HttpMultiClient;
use Cache\Adapter\Predis\PredisCachePool;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;

/**
 * Class BaseClient
 *
 * @package Ams\Domain\Client\Adapter
 */
abstract class BaseClient implements ClientInterface
{

    const CACHE_TTL_ONE_HOUR = 3600;
    const CACHE_TTL_FOUR_HOURS = 14400;
    const HTTP_METHOD = 'GET';
    const FIELD_DATA = 'data';
    const FIELD_META = 'meta';
    const FIELD_REQUEST = 'request';
    const FIELD_YEAR = 'year';
    const FIELD_SOURCE_ID = 'sourceId';
    const FIELD_LIMIT = 'limit';
    const FIELD_NUMBER = 'number';
    const FIELD_DATE = 'date';
    const FIELD_NAME = 'name';
    const FIELD_LINK = 'link';
    const FIELD_DETAILS = 'details';
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var PredisCachePool
     */
    protected $cache;

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
     * @var ContainerInterface
     */
    protected $container = null;

    public function __construct()
    {
        $this->setDiContainer();
    }

    /**
     * @param $client
     * @param $year
     * @param $limit
     * @param $data
     * @return array
     */
    protected function buildResponse(string $client, int $year, int $limit, array $data): array
    {
        return [
            self::FIELD_META => [
                self::FIELD_REQUEST => [
                    self::FIELD_SOURCE_ID => $client,
                    self::FIELD_YEAR => $year,
                    self::FIELD_LIMIT => $limit,
                    self::FIELD_DATA => $data
                ]
            ]
        ];
    }

    /**
     * @param $number
     * @param $year
     * @param $title
     * @param $link
     * @param $text
     * @return array
     */
    protected function assembleResponseBody($number, $year, $title, $link, $text) : array
    {
        return [
            self::FIELD_NUMBER => $number,
            self::FIELD_DATE => $year,
            self::FIELD_NAME => $title,
            self::FIELD_LINK => $link,
            self::FIELD_DETAILS => $text
        ];
    }

    /**
     * @return PredisCachePool
     */
    public function getCache(): PredisCachePool
    {
        return $this->cache = $this->cache ?? $this->getDiContainer()->get(PredisCachePool::class);
    }

    /**
     * @return Psr17Factory
     */
    public function getRequestFactory(): Psr17Factory
    {
        return $this->requestFactory = $this->requestFactory ?? (new Psr17Factory);
    }

    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpClient
     */
    public function getHttpClient(Psr17Factory $psr17Factory): Psr18HttpClient
    {
        return $this->httpClient = $this->httpClient ?? (new Psr18HttpClient($psr17Factory));
    }


    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpMultiClient
     */
    public function getHttpMultiClient(Psr17Factory $psr17Factory): Psr18HttpMultiClient
    {
        return $this->httpMultiClient = $this->httpMultiClient ?? (new Psr18HttpMultiClient($psr17Factory));
    }

    /**
     * @param string $client
     * @param int    $year
     * @return string
     */
    protected function assembleCacheKey(string $client, int $year): string
    {
        return implode([$client, $year], '-');
    }

    /**
     * @return ContainerInterface|null
     */
    public function setDiContainer()
    {
        return $this->container = $this->container ?? AppFactory::create()->getContainer();
    }

    /**
     * @return ContainerInterface
     */
    public function getDiContainer()
    {
        return $this->container;
    }
}
