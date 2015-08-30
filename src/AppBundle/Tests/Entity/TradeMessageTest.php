<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\TradeMessage;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * TradeMessageTest
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class TradeMessageTest extends WebTestCase
{
    /**
     * testFromArray
     *
     * Ensure all properties are being
     * set on object instance from data
     * provided as an associative array.
     */
    function testFromArray()
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

        $message = new TradeMessage();
        $message->fromArray($data);

        // get property values, excluding `id`
        $props = $message->toArray();
        unset($props['id']);

        $this->assertSame($data, $props);
    }

    /**
     * testInvalidDateTransformation
     *
     * Ensure that an invalid date, according
     * to the specified format of `d-M-y H:i:s`
     * throws an `\InvalidArgumentException`.
     *
     * @dataProvider      providerInvalidDateTransformation
     * @expectedException InvalidArgumentException
     */
    function testInvalidDateTransformation($timePlaced)
    {
        $message = new TradeMessage();

        $message->setTimePlaced($timePlaced);
        $message->transformData();
    }

    /**
     * testValidDateTransformation
     *
     * Ensure that the required data
     * transformations are applied to
     * specific fields; e.g: `timePlaced`
     *
     * @dataProvider providerValidDateTransformation
     */
    function testValidDateTransformation($input, $expected)
    {
        $message = new TradeMessage();

        $message->setTimePlaced($input);
        $message->transformData();

        $timePlaced = $message->getTimePlaced();

        $this->assertInstanceOf('\\DateTime', $timePlaced);
        $this->assertSame($timePlaced->format('Y-m-d H:i:s'), $expected);
    }

    /**
     * providerInvalidDateTransformation
     *
     * @return array
     */
    public function providerInvalidDateTransformation()
    {
        return [
            [null],
            [false],
            [''],
            [1],
            [1.0],
            ['24-JAN-2015 10:27:44'],
            ['2015-01-01 00:00:00'],
            // etc.
        ];
    }

    /**
     * providerValidDateTransformation
     *
     * @return array
     */
    public function providerValidDateTransformation()
    {
        return [
            ['24-JAN-15 10:27:44', '2015-01-24 10:27:44'],
            ['1-JAN-15 1:27:44',   '2015-01-01 01:27:44'],
            ['01-JAN-15 01:27:44', '2015-01-01 01:27:44'],
        ];
    }
}
