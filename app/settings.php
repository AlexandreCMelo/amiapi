<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    //$redisConfig = $isDevEnviroment =

    $containerBuilder->addDefinitions([
        'settings' => [
            'redis' => [
                'schema' => 'tcp',
                'host' => 'amsapi-redis',
                'port' => 6379,
            ],
            'redis_prod' => [
                'schema' => 'tcp',
                'host' => 'redis://h:p4e709c665e236aea3a0eafec990184f7d52c5e4a67c8b46bb918e98a4f5b2679@ec2-18-232-242-255.compute-1.amazonaws.com',
                'port' => 29169,
            ],
            'productionUri' => 'https://amsapi.herokuapp.com',
            'isDevEnviroment' => false,
            'displayErrorDetails' => true,
            'logger' => [
                'name' => 'ams-logs',
                'path' => __DIR__ . '/../logs/app.log',
                'level' => Logger::INFO,
            ],
        ],
    ]);
};
