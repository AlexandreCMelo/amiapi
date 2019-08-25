<?php
declare(strict_types=1);

namespace Ams\Application\Actions\Client;

use Ams\Application\Actions\Action;
use Ams\Domain\Client as ClientDomain;
use Cache\Adapter\Predis\PredisCachePool;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class Get extends Action
{

    const MAX_LIMIT = 9999;

    /**
     * ClientAction constructor.
     * @param LoggerInterface $logger
     * @param PredisCachePool $cachePool
     */
    public function __construct(LoggerInterface $logger, PredisCachePool $cachePool)
    {
        parent::__construct($logger, $cachePool);
    }

    /**
     * {@inheritdoc}
     * @throws HttpNotFoundException
     */
    protected function action(): Response
    {
        $year = $this->resolveArg('year');
        if (!$this->validateYear($year)) {
            throw New HttpBadRequestException($this->request, 'Please inform an valid year');
        }

        $limit = (int)$this->resolveArg('limit');
        if($limit > self::MAX_LIMIT) {
            throw New HttpBadRequestException($this->request, 'Limit must be under 9999');
        }

        $sourceId = $this->resolveArg('client');

        $response = $this->getClientDomain()->request($sourceId, (int)$year, $limit);

        return $this->respond($response);
    }

    /**
     * @param $year
     * @return bool|DateTime
     */
    protected function validateYear($year)
    {
        return DateTime::createFromFormat('Y', $year);
    }

    /**
     * @return ClientDomain
     */
    protected function getClientDomain(): ClientDomain
    {
        return $this->loadDomain('Client');
    }
}
