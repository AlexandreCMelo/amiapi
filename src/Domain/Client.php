<?php
declare(strict_types=1);

namespace Ams\Domain;

use Ams\Domain\Client\Adapter\ClientInterface;
use Ams\Domain\Client\Adapter\Spacex;
use Ams\Domain\Client\Adapter\Xkcd;
use Ams\Domain\DomainException\DomainRecordNotFoundException;

class Client
{
    /**
     * Expected clients
     * Could be easily extended
     *
     * @var array
     */
    private $clients = [
        Spacex::PARAM_CLIENT_KEY => Spacex::class,
        Xkcd::PARAM_CLIENT_KEY => Xkcd::class
    ];

    /**
     * @param string $source
     * @param int $year
     * @param int $limit
     * @return mixed
     * @throws DomainRecordNotFoundException
     */
    public function request(string $source, int $year, int $limit)
    {
        return $this->getClientAdapter($source)->request($year, $limit);
    }

    /**
     * @param string $source
     * @return bool
     * @throws DomainRecordNotFoundException
     */
    public function validateClient(string $source)
    {
        if (isset($this->clients[$source])) {
            return true;
        }

        throw new DomainRecordNotFoundException('Not found.');
    }

    /**
     * @param string $source
     * @return ClientInterface
     * @throws DomainRecordNotFoundException
     */
    public function getClientAdapter(string $source): ClientInterface
    {
        $this->validateClient($source);
        $client = new $this->clients[$source];

        return $client;
    }
}
