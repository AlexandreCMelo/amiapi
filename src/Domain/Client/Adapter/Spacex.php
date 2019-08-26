<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Adapter;

use DateTime;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Exception\HttpNotFoundException;

class Spacex extends BaseClient
{

    const PARAM_CLIENT_KEY = 'space';
    const PARAM_URL = 'https://api.spacexdata.com/v2/launches';
    const CACHE_MISSIONS = 'missions';

    /**
     * @param int $year
     * @param int $limit
     * @return 0|array|bool
     * @throws Exception
     */
    public function request(int $year, int $limit)
    {
        $launches = $this->getCachedMissions() ?? $this->requestMissionsJsonFromApi();
        $missions = $this->getCache()->get(self::CACHE_MISSIONS) ?? $this->assembleMissions($launches);

        if (empty($missions[$year])) {
            return false;
        }

        $data = array_slice($missions[$year], 0, $limit);

        $data = $this->buildResponse(
            self::PARAM_CLIENT_KEY,
            $year,
            $limit,
            $data
        );

        return $data;
    }

    /**
     * @return array
     */
    protected function requestMissionsJsonFromApi(): array
    {
        $requestFactory = $this->getRequestFactory();
        $request = $requestFactory->createRequest(
            self::HTTP_METHOD,
            self::PARAM_URL
        );

        $response = $this->getHttpClient($requestFactory)->sendRequest($request);

        $missions = json_decode($response->getBody()->getContents());
        $this->setCachedMissions($missions);

        return $missions;
    }

    /**
     * @param $launches
     * @return array
     * @throws Exception
     */
    protected function assembleMissions($launches): array
    {
        $missions = [];
        foreach ($launches as $launch) {
            $date = (new DateTime($launch->launch_date_utc))->format(self::DATE_FORMAT);
            $missions[$launch->launch_year][] = $this->assembleResponseBody(
                $launch->flight_number,
                $date,
                $launch->mission_name,
                $launch->links->article_link,
                $launch->details
            );
        }

        return $missions;
    }

    /**
     * @return mixed|void|null
     * @throws InvalidArgumentException
     */
    public function getCachedMissions()
    {
        return $this->getCache()->get(self::PARAM_CLIENT_KEY);
    }

    /**
     * @param $missions
     * @throws InvalidArgumentException
     */
    public function setCachedMissions($missions): void
    {
        $this->getCache()->set(self::PARAM_CLIENT_KEY, $missions, self::CACHE_TTL_FOUR_HOURS);
    }
}
