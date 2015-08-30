<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\TradeMessage;
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
}
