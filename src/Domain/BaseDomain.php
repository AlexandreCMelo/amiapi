<?php
declare(strict_types=1);

namespace Ams\Domain;

use Cache\Adapter\Predis\PredisCachePool;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;

Abstract class BaseDomain
{

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PredisCachePool
     */
    protected $cache;

    /**
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return BaseDomain
     */
    public function setLogger(LoggerInterface $logger): BaseDomain
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return PredisCachePool
     */
    public function getCache() : PredisCachePool
    {
        return $this->cache;
    }

    /**
     * @param PredisCachePool $cache
     * @return BaseDomain
     */
    public function setCache(PredisCachePool $cache): BaseDomain
    {
        $this->cache = $cache;
        return $this;
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
     * @return BaseDomain
     */
    public function setRequest(ServerRequestInterface $request): baseDomain
    {
        $this->request = $request;
        return $this;
    }

}