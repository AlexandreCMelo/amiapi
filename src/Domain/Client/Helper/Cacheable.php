<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Helper;

use Cache\Adapter\Predis\PredisCachePool;

trait Cacheable{

    /**
     * @var PredisCachePool
     */
    protected $cache;

    /**
     * @return PredisCachePool
     */
    function getCache(): PredisCachePool
    {
        return $this->cache = $this->cache ?? (New PredisCachePool());
    }

    /**
     * @param PredisCachePool $cache
     */
    function setCache(PredisCachePool $cache)
    {
        $this->cache = $cache;
    }
}