<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'redis' => [
                'schema' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
            ],
            'displayErrorDetails' => true,
            'logger' => [
                'name' => 'ams-logs',
                'path' => __DIR__ . '/../logs/app.log',
                'level' => Logger::INFO,
            ],
        ],
    ]);
};
