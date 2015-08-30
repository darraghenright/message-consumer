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
     * testValidatorCurrencyFrom
     *
     *@dataProvider providerValidatorCurrency
     */
    function testValidatorCurrencyFrom($currencyFrom, $expected)
    {
        $message = (new TradeMessage())
            ->setCurrencyFrom($currencyFrom);

        $validator = static::createClient()->getContainer()->get('validator');

        $errors = $validator->validate($message, ['currencyFrom']);
        $result = 0 === $errors->count();

        $this->assertSame($expected, $result);
    }

    /**
     * testValidatorCurrencyTo
     *
     * @dataProvider providerValidatorCurrency
     */
    function testValidatorCurrencyTo($currencyTo, $expected)
    {
        $message = (new TradeMessage())
            ->setCurrencyTo($currencyTo);

        $validator = static::createClient()->getContainer()->get('validator');

        $errors = $validator->validate($message, ['currencyTo']);
        $result = 0 === $errors->count();

        $this->assertSame($expected, $result);
    }

    /**
     * testValidatorOriginatingCountry
     *
     * @dataProvider providerValidatorOriginatingCountry
     */
    function testValidatorOriginatingCountry($originatingCountry, $expected)
    {
        $message = (new TradeMessage())
            ->setOriginatingCountry($originatingCountry);

        $validator = static::createClient()->getContainer()->get('validator');

        $errors = $validator->validate($message, ['originatingCountry']);
        $result = 0 === $errors->count();

        $this->assertSame($expected, $result);
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

    /**
     * providerValidatorCurrencyTo
     *
     * @return array
     */
    public function providerValidatorCurrency()
    {
        return [
            ['EUR',  true],
            ['GBP',  true],
            ['USD',  true],
            ['XYZ', false],
            ['123', false],
            ['A',   false],
            ['UK',  false],
            ['...', false],
            ['.',   false],
            ['',    false],
            [null,  false],
            [false, false],
            [1,     false],
            // etc.
        ];
    }

    /**
     * providerValidatorOriginatingCountry
     *
     * @return array
     */
    public function providerValidatorOriginatingCountry()
    {
        return [
            ['IE',   true],
            ['GB',   true],
            ['US',   true],
            ['XY',  false],
            ['I',   false],
            ['EUR', false],
            ['.',   false],
            ['',    false],
            [null,  false],
            [false, false],
            [1,     false],
            // etc.
        ];
    }
}
