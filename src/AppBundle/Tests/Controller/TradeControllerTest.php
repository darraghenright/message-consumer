<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * TradeControllerTest
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class TradeControllerTest extends WebTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $this->endpoint = '/trade/message/';
    }

    /**
     * testEndpointExists
     *
     * Ensure that the specified endpoint
     * does not return `404 Not Found`.
     */
    public function testEndpointExists()
    {
        $this->client->request('HEAD', $this->endpoint);
        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertNotEquals(404, $statusCode);
    }

    /**
     * testMethodNotAllowed
     *
     * Ensure that only method POST is
     * accepted. All other methods should
     * return status `405 Method Not Allowed`.
     *
     * @dataProvider providerMethodNotAllowed
     *
     * @param string $method
     */
    public function testMethodNotAllowed($method)
    {
        $this->client->request($method, $this->endpoint);
        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertEquals(405, $statusCode);
    }

    /**
     * testAllowMethodPost
     *
     * Ensure that only method POST is
     * accepted. All other methods should
     * return status `405 Method Not Allowed`.
     */
    public function testMethodPostIsAllowed()
    {
        $this->client->request('POST', $this->endpoint);
        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertNotEquals(405, $statusCode);
    }

    /**
     * testContentType
     *
     * Ensure that the request contains Content-Type
     * of `application/json`. Otherwise return status
     * `400 Bad Request` with error message.
     */
    public function testContentType()
    {
        $this->client->request('POST', $this->endpoint, [], [], ['Content-Type'  => 'plain/text']);
        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertSame(400, $statusCode);
        // $this->assertSame(); -> string
    }

    public function testJsonValidation()
    {

    }

    /**
     * providerMethodNotAllowed
     *
     * @return array
     */
    public function providerMethodNotAllowed()
    {
        return [
            ['DELETE'],
            ['GET'],
            ['HEAD'],
            ['PUT'],
            ['OPTIONS'],
            ['PATCH'],
        ];
    }
}
