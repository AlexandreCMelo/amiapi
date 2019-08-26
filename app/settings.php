<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions(
        [
        'settings' => [
            'redis' => [
                'schema' => 'tcp',
                'host' => getenv('REDIS_DEV_DOCKER_SERVICE_HOST'),
                'port' => getenv('REDIS_DEV_DOCKER_SERVICE_PORT'),
            ],
            'redis_prod' => [
                'schema' => 'tcp',
                'host' => getenv('REDIS_PROD_HOST'),
                'port' => getenv('REDIS_PROD_PORT'),
            ],
            'productionUri' => getenv('PRODUCTION_HEROKU_URI'),
            'isDevEnviroment' => getenv('ENVIROMENT_DEVELOPMENT') === 'true'? true: false,
            'displayErrorDetails' => getenv('SHOW_ERRORS') === 'true'? true: false
            ,
            'logger' => [
                'name' => getenv('LOG_NAME'),
                'path' => __DIR__ . '/'. getenv('LOG_PATH'),
                'level' => getenv('LOG_LEVEL'),
            ],
        ],
        ]
    );
};
