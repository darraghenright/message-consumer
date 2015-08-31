<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\TradeMessage;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadTradeMessageFixtures
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class LoadTradeMessageFixtures implements FixtureInterface
{
    /**
     * Placeholder user attributes
     *
     * @var array
     */
    private $users = [
        [
            'userId'             => '111111',
            'originatingCountry' => 'AU',
            'currencyFrom'       => 'AUD',
        ],
        [
            'userId'             => '222222',
            'originatingCountry' => 'CA',
            'currencyFrom'       => 'CAD',
        ],
        [
            'userId'             => '333333',
            'originatingCountry' => 'IE',
            'currencyFrom'       => 'EUR',
        ],
        [
            'userId'             => '444444',
            'originatingCountry' => 'GB',
            'currencyFrom'       => 'GBP',
        ],
        [
            'userId'             => '555555',
            'originatingCountry' => 'US',
            'currencyFrom'       => 'USD',
        ],
    ];

    /**
     * Currency exchange matrix.
     * Approx rates for 2015-08-31.
     *
     * @var array
     */
    private $rates = [

        'AUD' => [
            'CAD' => 0.93512,
            'GBP' => 0.46321,
            'EUR' => 1.36705,
            'USD' => 1.53466,
        ],

        'CAD' => [
            'AUD' => 1.06938,
            'GBP' => 0.49534,
            'EUR' => 1.36705,
            'USD' => 1.53466,
        ],

        'GBP' => [
            'AUD' => 2.15887,
            'CAD' => 2.01880,
            'EUR' => 1.36705,
            'USD' => 1.53466,
        ],

        'EUR' => [
            'AUD' => 1.57715,
            'CAD' => 1.47483,
            'GBP' => 0.731501,
            'USD' => 1.12275,
        ],

        'USD' => [
            'AUD' => 1.40528,
            'CAD' => 1.31428,
            'EUR' => 0.890672,
            'GBP' => 0.651609,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        // boost memory limit for fixture generation
        ini_set('memory_limit', '1G');

        // create a date period spanning three months
        $dp = new DatePeriod(
            new DateTime('4 months ago'),
            new DateInterval('P1D'),
            new DateTime()
        );

        // iterate over each datetime
        foreach (iterator_to_array($dp) as $dt) {

            // create trade messages for each user
            foreach ($this->users as $user) {

                 // send a variable number of messages per user
                $sendMessages = mt_rand(10, 50);

                while ($sendMessages--) {

                    extract($user);

                    $currencyTo = array_rand($this->rates[$currencyFrom]);
                    $rate = $this->rates[$currencyFrom][$currencyTo];

                    // add some very primitive randomness...
                    // mock out rate flucutations over time.
                    $rate = round($rate + mt_rand(-99, 99) / 999, 6);

                    $message = (new TradeMessage())
                        ->setCurrencyFrom($currencyFrom)
                        ->setCurrencyTo($currencyTo)
                        ->setOriginatingCountry($originatingCountry)
                        ->setRate($rate) // @TODO randomise per day?
                        ->setTimePlaced($this->getRandomisedTimePlaced($dt))
                        ->setUserId($userId);

                    $amountSell = mt_rand(100, 100000);
                    $amountBuy = $amountSell * $message->getRate();

                    $message
                        ->setAmountSell($amountSell)
                        ->setAmountBuy($amountBuy);

                    $em->persist($message);
                }
            }
        }

        $em->flush();
    }

    /**
     * getRandomisedTimePlaced
     *
     * Clone current datetime and add a naive random
     * modification in hours, minutes and/or seconds.
     *
     * Naive, in that, this might return a new DateTime
     * object for the previous, or next, day.
     *
     * This method:
     *
     * * Creates randomly filtered array of durations; e.g: `['hours', 'seconds']`
     * * Assigns signed/unsigned times to each duration; e.g: `['2 hours', '-34 seconds']`
     * * Implodes times into a modify string: `'2 hours -34 seconds'`
     * * If the modify string is not empty, clone and modify the datetime
     * * Return datetime
     *
     * Trivia corner: passing a blank string to DateTime::modify()
     * is a PHP warning. But an otherwise empty string with a newline
     * is completely valid...
     *
     * @param  \DateTime $dt
     * @return \DateTime
     */
    function getRandomisedTimePlaced(DateTime $dt)
    {
        $durations = array_filter(['hours', 'minutes', 'seconds'], function() {
            return mt_rand(0, 1) === 1;
        });

        $times = array_map(function($d) {
            $s = array_rand(array_flip(['', '-']));  // signed?
            $t = mt_rand(0, 'hours' === $d ? 23: 59); // t?
            return sprintf('%s%d %s', $s, $t, $d);
        }, $durations);

        $modify = implode(' ', $times);

        if (0 !== strlen($modify)) {
            $dt = clone $dt;
            $dt->modify($modify);
        }

        return $dt;
    }
}
