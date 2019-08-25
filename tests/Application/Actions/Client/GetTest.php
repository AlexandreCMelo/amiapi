<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Client;

use Ams\Domain\Client\Adapter\Spacex;
use Ams\Domain\Client\Adapter\Xkcd;
use Cache\Adapter\Predis\PredisCachePool;
use DI\Container;
use Tests\TestCase;

class GetTest extends TestCase
{
    /**
     * Expected clients to test
     * Should be extended
     * @var array
     */
    public $clients = [
        Spacex::PARAM_CLIENT_KEY => Spacex::class,
        Xkcd::PARAM_CLIENT_KEY => Xkcd::class
    ];

    public function testing_spacex_get_endpoint()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        /**
         * @var PredisCachePool
         */
        $cache = $container->get(PredisCachePool::class);


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

        $urlPattern = '/api/client/%s/year/%d/limit/%d';
        foreach ($testingConstrains as $testingConstrain) {

            $url = vsprintf($urlPattern, [Spacex::PARAM_CLIENT_KEY, $testingConstrain['year'], $testingConstrain['limit']]);

            $response = $this->createRequest('GET', $url);
            $responseBodyJson = json_decode($response->getBody()->getContents());

            $this->writeMessage('Testing endpoint ' . $url);
            $this->assertEquals(self::HTTP_SUCCESS_CODE, $response->getStatusCode(), 'teste');

            $this->writeMessage('Testing result count should be ' .$testingConstrain['limit']. ' is '. count($responseBodyJson->meta->request->data));
            $this->assertCount($testingConstrain['limit'], $responseBodyJson->meta->request->data);
        }
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testing_spacex_get_invalid_endpoint()
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

        $urlPattern = '/api/client/%s/year/%d/limit/%d';
        foreach ($testingConstrains as $testingConstrain) {

            $url = vsprintf($urlPattern, [Spacex::PARAM_CLIENT_KEY, $testingConstrain['year'], $testingConstrain['limit']]);

            $response = $this->createRequest('GET', $url);
            $responseBodyJson = json_decode($response->getBody()->getContents());

            $this->writeMessage('Testing invalid endpoint ' . $url. ' should return 404');
            $this->assertEquals(self::HTTP_NOT_FOUND, $response->getStatusCode());
            $this->assertContains('RESOURCE_NOT_FOUND', $responseBodyJson->error->type);
        }
    }


}