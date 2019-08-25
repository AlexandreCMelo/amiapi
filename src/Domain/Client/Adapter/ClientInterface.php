<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Adapter;

use Cache\Adapter\Predis\PredisCachePool;
use Psr\Log\LoggerInterface;

Interface ClientInterface
{
    public function request(int $year, int $limit);
}