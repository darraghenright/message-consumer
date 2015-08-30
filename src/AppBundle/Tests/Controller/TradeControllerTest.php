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
     * @string
     */
    const ERR_CONTENT_TYPE = '{"message":"Content-Type must be application\/json"}';

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
     * testInvalidRequestContentType
     *
     * Ensure that the request contains Content-Type
     * of `application/json`. Otherwise return status
     * `400 Bad Request` with error message.
     */
    public function testInvalidRequestContentType()
    {
        $server  = ['CONTENT_TYPE'  => 'plain/text'];
        $content = '{}';

        $this->client->request('POST', $this->endpoint, [], [], $server, $content);

        $request  = $this->client->getRequest();
        $response = $this->client->getResponse();

        $this->assertFalse($request->headers->contains('Content-Type', 'application/json'));
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(self::ERR_CONTENT_TYPE, $response->getContent());
    }

    /**
     * testValidRequestContentType
     *
     * Ensure that the request contains Content-Type
     * of `application/json`.
     */
    public function testValidRequestContentType()
    {
        $server  = ['CONTENT_TYPE'  => 'application/json'];
        $content = '{}';

        $this->client->request('POST', $this->endpoint, [], [], $server, $content);

        $request  = $this->client->getRequest();
        $response = $this->client->getResponse();

        $this->assertTrue($request->headers->contains('Content-Type', 'application/json'));
        $this->assertNotSame(self::ERR_CONTENT_TYPE, $response->getContent());
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
