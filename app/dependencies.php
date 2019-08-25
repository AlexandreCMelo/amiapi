<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
    ]);

    $containerBuilder->addDefinitions([
        Cache\Adapter\Predis\PredisCachePool::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $redisSettings = $settings['isDevEnviroment'] ? $settings['redis'] : $settings['redis_prod'] ;
            $host = $redisSettings['host'].':'.$redisSettings['port'];
            $client = new \Predis\Client($host);

            $pool = new Cache\Adapter\Predis\PredisCachePool($client);

            return $pool;
        },
    ]);

};
