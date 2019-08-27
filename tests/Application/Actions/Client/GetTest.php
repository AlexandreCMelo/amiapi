<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Client;

use Ams\Domain\Client\Adapter\Spacex;
use Ams\Domain\Client\Adapter\Xkcd;
use Cache\Adapter\Predis\PredisCachePool;
use DateInterval;
use DateTime;
use DI\Container;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Tests\TestCase;

class GetTest extends TestCase
{
    /**
     * Expected clients to test
     * Should be extended
     *
     * @var array
     */
    public $clients = [
        Spacex::PARAM_CLIENT_KEY => Spacex::class,
        Xkcd::PARAM_CLIENT_KEY => Xkcd::class
    ];

    public function testingSpaceXEndPoint()
    {

        $testingConstrains = [
            [
                'year' => 2006,
                'limit' => 1,
            ],
            [
                'year' => 2010,
                'limit' => 2,
            ],
            [
                'year' => 2014,
                'limit' => 6,
            ],
            [
                'year' => 2018,
                'limit' => 11,
            ],
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';
        foreach ($testingConstrains as $testingConstrain) {
            $url = vsprintf(
                $urlPattern,
                [
                    Spacex::PARAM_CLIENT_KEY,
                    $testingConstrain['year'],
                    $testingConstrain['limit']
                ]
            );

            $response = $this->createRequest('GET', $url);
            $responseBodyJson = json_decode($response->getBody()->getContents());

            $this->assertEquals(self::HTTP_SUCCESS_CODE, $response->getStatusCode(), $url);
            $dataCount = count($responseBodyJson->meta->request->data);
            $this->assertCount($testingConstrain['limit'], $responseBodyJson->meta->request->data);
        }
    }

    /**
     * @throws Exception
     */
    public function testingSpaceXInvalidEndpoint()
    {

        $testingConstrains = [
            [
                'year' => '20018',
                'limit' => 'an_invalid_limit',
            ],
            [
                'year' => '1000',
                'limit' => '10',
            ],
            [
                'year' => 'an_invalid_year',
                'limit' => '21',
            ]
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';
        foreach ($testingConstrains as $testingConstrain) {
            $url = vsprintf(
                $urlPattern,
                [
                    Spacex::PARAM_CLIENT_KEY,
                    $testingConstrain['year'],
                    $testingConstrain['limit']
                ]
            );

            $response = $this->createRequest('GET', $url);
            $responseBodyJson = json_decode($response->getBody()->getContents());
            $this->assertEquals(self::HTTP_NOT_FOUND, $response->getStatusCode());
            $this->assertContains('RESOURCE_NOT_FOUND', $responseBodyJson->error->type);
        }
    }

    public function testingXkcdEndPoint()
    {

        $testingConstrains = [
            [
                'year' => 2008,
                'limit' => 8,
            ],
            [
                'year' => 2012,
                'limit' => 2,
            ],
            [
                'year' => 2017,
                'limit' => 6,
            ],
            [
                'year' => 2019,
                'limit' => 20,
            ],
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';
        foreach ($testingConstrains as $testingConstrain) {
            $url = vsprintf(
                $urlPattern,
                [
                    Xkcd::PARAM_CLIENT_KEY,
                    $testingConstrain['year'],
                    $testingConstrain['limit']
                ]
            );

            $response = $this->createRequest('GET', $url);
            $responseBodyJson = json_decode($response->getBody()->getContents());

            $this->assertEquals(self::HTTP_SUCCESS_CODE, $response->getStatusCode(), $url);
            $dataCount = count($responseBodyJson->meta->request->data);
            $this->assertCount($testingConstrain['limit'], $responseBodyJson->meta->request->data);
        }
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testingInvalidDomains()
    {

        $testingConstrains = [
            'client' => 'invalidus',
            'year' => '2009',
            'limit' => '10',
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';

        $url = vsprintf(
            $urlPattern,
            [
                $testingConstrains['client'],
                $testingConstrains['year'],
                $testingConstrains['limit']
            ]
        );


        $response = $this->createRequest('GET', $url);
        $responseBodyJson = json_decode($response->getBody()->getContents());

        $this->assertEquals(self::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertContains('RESOURCE_NOT_FOUND', $responseBodyJson->error->type);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testingNonExistentYearSpaceX()
    {
        $nextYear = new DateTime();
        $nextYear->add(new DateInterval('P1Y'));

        $testingConstrains = [
            'client' => 'space',
            'year' => $nextYear->format('Y'),
            'limit' => '10',
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';

        $url = vsprintf(
            $urlPattern,
            [
                $testingConstrains['client'],
                $testingConstrains['year'],
                $testingConstrains['limit']
            ]
        );

        $response = $this->createRequest('GET', $url);
        $responseBodyJson = json_decode($response->getBody()->getContents());
        $this->assertEquals(self::HTTP_NOTE_VALID_CODE, $response->getStatusCode());
        $this->assertContains('BAD_REQUEST', $responseBodyJson->error->type);

    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testingNonExistentYearXkcd()
    {
        $nextYear = new DateTime();
        $nextYear->add(new DateInterval('P1Y'));

        $testingConstrains = [
            'client' => 'comics',
            'year' => $nextYear->format('Y'),
            'limit' => '10',
        ];

        $urlPattern = $this->url() . '/api/client/%s/year/%d/limit/%d';

        $url = vsprintf(
            $urlPattern,
            [
                $testingConstrains['client'],
                $testingConstrains['year'],
                $testingConstrains['limit']
            ]
        );

        $response = $this->createRequest('GET', $url);
        $responseBodyJson = json_decode($response->getBody()->getContents());
        $this->assertEquals(self::HTTP_NOTE_VALID_CODE, $response->getStatusCode());
        $this->assertContains('BAD_REQUEST', $responseBodyJson->error->type);

    }
}
