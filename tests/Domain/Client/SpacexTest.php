<?php

declare(strict_types=1);

namespace Tests\Domain\Client;

use Ams\Domain\Client\Adapter\Spacex;
use Buzz\Client\Curl;
use Buzz\Client\MultiCurl;
use Cache\Adapter\Predis\PredisCachePool;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\TestCase;

class SpacexTest extends TestCase
{

    /**
     * @var array
     */
    protected $request = [
        'year' => 2018,
        'limit' => 20,
    ];

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCanCallCacheClass(){
        $spaceClass = new Spacex();
        $this->writeMessage('Testing can access cache class');
        $this->assertInstanceOf(PredisCachePool::class, $spaceClass->getCache());
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCanCallHttpClientClass(){
        $spaceClass = new Spacex();
        $this->writeMessage('Testing can access http class');
        $requestFactory = new Psr17Factory();
        $this->assertInstanceOf(Curl::class, $spaceClass->getHttpClient($requestFactory));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCanCallMultiHttpClientClass(){
        $spaceClass = new Spacex();
        $this->writeMessage('Testing can access multi http class');
        $requestFactory = new Psr17Factory();
        $this->assertInstanceOf(MultiCurl::class, $spaceClass->getHttpMultiClient($requestFactory));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCanMakeRequest(){
        $spaceClass = new Spacex();
        $this->writeMessage('Testing can make a request');
        $requestFactory = new Psr17Factory();
        $client =  $spaceClass->getHttpMultiClient($requestFactory);
        $request = $requestFactory->createRequest(
            'GET',
            'https://amsapi.herokuapp.com/api/client/space/year/2008/limit/1'
        );

        $response = $client->sendRequest($request);
        $body = json_decode($response->getBody()->getContents());
        $data = $body->meta->request->data[0];

        $this->writeMessage('Status code should be 200');
        $this->assertEquals(self::HTTP_SUCCESS_CODE, $response->getStatusCode());

        $this->writeMessage('Response body must have right fields');
        $this->assertObjectHasAttribute('number', $data);
        $this->assertObjectHasAttribute('name', $data);
        $this->assertObjectHasAttribute('date', $data);
        $this->assertObjectHasAttribute('link', $data);
        $this->assertObjectHasAttribute('details', $data);

    }


}
