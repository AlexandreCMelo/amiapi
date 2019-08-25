<?php
declare(strict_types=1);

namespace Ams\Application\Actions;

use Ams\Domain\BaseDomain;
use Ams\Domain\Client;
use Ams\Domain\DomainException\DomainRecordNotFoundException;
use Cache\Adapter\Predis\PredisCachePool;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Action
{

    const DOMAIN_PATH = 'Ams\\Domain\\';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PredisCachePool
     */
    protected $cache;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var BaseDomain
     */
    protected $domain;

    /**
     * Action constructor.
     * @param LoggerInterface $logger
     * @param PredisCachePool $cache
     */
    public function __construct(LoggerInterface $logger, PredisCachePool $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @return Response
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @param string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param $payload
     * @return Response
     */
    protected function respond($payload): Response
    {
        $payload = new ActionPayload(200, $payload);
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);
        return $this->response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param $name
     * @return BaseDomain | Client
     */
    protected function loadDomain($name)
    {
        if ($this->domain == null) {
            $domainClass = self::DOMAIN_PATH . $name;

            /**
             * @var BaseDomain $domain
             */
            $domain = new $domainClass();
            $domain->setCache($this->cache)
                ->setLogger($this->logger)
                ->setRequest($this->request);

            $this->domain = $domain;
        }

        return $this->domain;
    }


}
