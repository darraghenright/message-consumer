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
     * @string
     */
    const ERR_JSON_MALFORMED = 'JSON parse error';

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
     * testMethodPostIsAllowed
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
        $message  = json_decode($response->getContent())->message;

        $this->assertFalse($request->headers->contains('Content-Type', 'application/json'));
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(self::ERR_CONTENT_TYPE, $response->getContent());
    }

    /**
     * testValidRequestContentType
     *
     * Ensure that the request contains
     * Content-Type of `application/json`.
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
     * testMalformedJsonIsBadRequest
     *
     * JSON that cannot be parsed should
     * return a status of `400 Bad Request`
     * with an error message; e.g:
     *
     * * JSON parse error (Syntax Error)
     * * JSON parse error (Syntax Error, Malformed JSON)
     *
     * This test does a partial match on the error
     * string and does not validate the specific error
     * message returned by `json_last_error_msg()`.
     *
     * @dataProvider providerMalformedJsonIsBadRequest
     */
    public function testMalformedJsonIsBadRequest($content)
    {
        $server  = ['CONTENT_TYPE'  => 'application/json'];

        $this->client->request('POST', $this->endpoint, [], [], $server, $content);

        $response = $this->client->getResponse();
        $message  = json_decode($response->getContent())->message;

        $this->assertContains(self::ERR_JSON_MALFORMED, $message);
        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * testCompleteRequest
     *
     * @dataProvider providerCompleteRequest
     */
    public function testCompleteRequest($content, $statusCode)
    {
        $server  = ['CONTENT_TYPE'  => 'application/json'];

        $this->client->request('POST', $this->endpoint, [], [], $server, $content);

        $response = $this->client->getResponse();
        //$message  = json_decode($response->getContent())->message;

        //$this->assertContains(self::ERR_JSON_MALFORMED, $message);
        $this->assertSame($statusCode, $response->getStatusCode());
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

    /**
     * providerMalformedJsonIsBadRequest
     *
     * @return array
     */
    public function providerMalformedJsonIsBadRequest()
    {
        return [
            ['.'],          // JSON_ERROR_SYNTAX
            ['{}]'],        // JSON_ERROR_STATE_MISMATCH
            ['\\uGGGG'],    // JSON_ERROR_UTF8
            ["{'foo': 1}"], // JSON_ERROR_SYNTAX
            // etc.
        ];
    }

    /**
     * providerCompleteRequest
     *
     * @return array
     */
    public function providerCompleteRequest()
    {
        $data = [
            'userId'             => '134256',
            'currencyFrom'       => 'EUR',
            'currencyTo'         => 'GBP',
            'amountSell'         => 1000,
            'amountBuy'          => 747.10,
            'rate'               => 0.7471,
            'timePlaced'         => '24-JAN-15 10:27:44',
            'originatingCountry' => 'FR',
        ];

        $requests = [
            [json_encode($data, JSON_NUMERIC_CHECK), 201]
        ];

        $keys = array_keys($data);

        foreach ($keys as $key) {
            $_data = $data;
            unset($_data[$key]);
            $requests[] = [json_encode($_data, JSON_NUMERIC_CHECK), 422];
        }

        return $requests;
    }
}
