<?php
declare(strict_types=1);

return function (Slim\App $app) {
    $app->get(
        '/api/client/{client}/year/{year:[\d+]{4}}/limit/{limit:\d+}',
        Ams\Application\Actions\Client\Get::class
    );
};
