<?php
declare(strict_types=1);

namespace Ams\Domain;

use Ams\Domain\Client\Adapter\ClientInterface;
use Ams\Domain\Client\Adapter\Spacex;
use Ams\Domain\Client\Adapter\Xkcd;
use Slim\Exception\HttpNotFoundException;

class Client extends BaseDomain
{
    /**
     * Expected clients
     * Could be easily extended
     * @var array
     */
    private $clients = [
        Spacex::PARAM_CLIENT_KEY => Spacex::class,
        Xkcd::PARAM_CLIENT_KEY => Xkcd::class
    ];

    /**
     * @param string $source
     * @param $year
     * @param int $limit
     * @return array
     * @throws HttpNotFoundException
     */
    public function request(string $source, int $year, int $limit): array
    {
        return $this->getClientAdapter($source)->request($year, $limit);
    }

    /**
     * @param string $source
     * @throws HttpNotFoundException
     */
    public function validateClient(string $source): void
    {
        if (empty($this->clients[$source])) {
            throw new HttpNotFoundException($this->getRequest(), 'Client not found.');
        }
    }

    /**
     * @param string $source
     * @return ClientInterface
     * @throws HttpNotFoundException
     */
    public function getClientAdapter(string $source): ClientInterface
    {
        $this->validateClient($source);
        $client = New $this->clients[$source];
        $client->setCache($this->getCache());
        $client->setLogger($this->getLogger());

        return $client;
    }
}
