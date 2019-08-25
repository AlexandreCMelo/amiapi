<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Adapter;

use Buzz\Client\Curl as Psr18HttpClient;
use Buzz\Client\MultiCurl as Psr18HttpMultiClient;
use Cache\Adapter\Predis\PredisCachePool;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseClient
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
     * @var LoggerInterface
     */
    protected $logger;

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
     * @var ServerRequestInterface
     */
    protected $request = null;

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
        return $this->cache ?? (New PredisCachePool);
    }

    /**
     * @param PredisCachePool $cache
     */
    public function setCache(PredisCachePool $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Psr17Factory
     */
    public function getRequestFactory(): Psr17Factory
    {
        return $this->requestFactory ?? (New Psr17Factory);
    }

    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpClient
     */
    public function getHttpClient(Psr17Factory $psr17Factory): Psr18HttpClient
    {
        return $this->httpClient ?? (New Psr18HttpClient($psr17Factory));
    }


    /**
     * @param Psr17Factory $psr17Factory
     * @return Psr18HttpMultiClient
     */
    public function getHttpMultiClient(Psr17Factory $psr17Factory): Psr18HttpMultiClient
    {
        return $this->httpMultiClient ?? (New Psr18HttpMultiClient($psr17Factory));
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

    /**
     * @param string $client
     * @param int $year
     * @return string
     */
    protected function assembleCacheKey(string $client, int $year): string
    {
        return implode([$client, $year], '-');
    }


}