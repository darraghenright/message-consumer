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
            'user_id'  => '10000',
            'country'  => 'IE',
            'currency' => 'EUR',
        ],
        [
            'user_id'  => '20000',
            'country'  => 'GB',
            'currency' => 'GBP',
        ],
        [
            'user_id'  => '30000',
            'country'  => 'US',
            'currency' => 'USD',
        ],
    ];


    /**
     * Currency exchange matrix
     *
     * @var array
     */
    private $rates = [
        'GBP' => [
            'EUR' => 1.36705,
            'USD' => 1.53466,
        ],

        'EUR' => [
            'GBP' => 0.731501,
            'USD' => 1.12275,
        ],

        'USD' => [
            'EUR' => 0.890672,
            'GBP' => 0.651609,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $dp = new DatePeriod(
            new DateTime('3 months ago'),
            new DateInterval('P1D'),
            new DateTime()
        );

        foreach (iterator_to_array($dp) as $date) {
            foreach ($this->users as $user) {

                $message = (new TradeMessage())
                    ->setCurrencyFrom($user[])
                    ->setCurrencyTo() // random
                    ->setTimePlaced()
                    ->setUserId()

                $em->persist($message);
            }
        }

        $em->flush();

    }
}
