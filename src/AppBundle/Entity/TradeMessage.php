<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TradeMessage
 *
 * @ORM\Table(name="trade_message")
 * @ORM\Entity
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class TradeMessage
{
    /**
     * @string
     */
    const FORMAT_TIME_PLACED = 'd-M-y H:i:s';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="string", length=255)
     *
     * @Assert\NotBlank(message="userId is blank")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_from", type="string", length=3)
     *
     * @Assert\NotBlank(message="currencyFrom is blank")
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\Iso", "getIso4217CurrencyCodes"},
     *     message="currencyFrom is not valid"
     * )
     */
    private $currencyFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_to", type="string", length=3)
     *
     * @Assert\NotBlank(message="currencyTo is blank")
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\Iso", "getIso4217CurrencyCodes"},
     *     message="currencyFrom is not valid"
     * )
     */
    private $currencyTo;

    /**
     * @var string
     *
     * @ORM\Column(name="amount_sell", type="decimal", scale=2)
     *
     * @Assert\NotBlank(message="amountSell is blank")
     */
    private $amountSell;

    /**
     * @var string
     *
     * @ORM\Column(name="amount_buy", type="decimal", scale=2)
     *
     * @Assert\NotBlank(message="amountBuy is blank")
     */
    private $amountBuy;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", scale=6)
     *
     * @Assert\NotBlank(message="rate is blank")
     */
    private $rate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_placed", type="datetime")
     *
     * @Assert\NotBlank(message="timePlaced is blank")
     */
    private $timePlaced;

    /**
     * @var string
     *
     * @ORM\Column(name="originating_country", type="string", length=2)
     *
     * @Assert\NotBlank(message="originatingCountry is blank")
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\Iso", "getIso3166CountryCodes"},
     *     message="currencyFrom is not valid"
     * )
     */
    private $originatingCountry;

    /**
     * fromArray
     *
     * Populate the current object
     * from an associative array of
     * values.
     *
     * @param array $data
     */
    public function fromArray(array $data)
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * transformData
     *
     * Apply transformations to fields
     * that require it, including:
     *
     * * Conversion of non-standard datetime string to \DateTime
     */
    public function transformData()
    {
        $timePlaced = DateTime::createFromFormat(
            self::FORMAT_TIME_PLACED, $this->timePlaced
        );

        if (false === $timePlaced) {
            throw new \InvalidArgumentException(
                sprintf('Invalid datetime provided (%s)', $this->timePlaced)
            );
        }

        $this->timePlaced = $timePlaced;
    }

    /**
     * toArray
     *
     * Retrieve an associative array
     * of the values currently set
     * for the current instance.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return TradeMessage
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set currencyFrom
     *
     * @param string $currencyFrom
     * @return TradeMessage
     */
    public function setCurrencyFrom($currencyFrom)
    {
        $this->currencyFrom = $currencyFrom;

        return $this;
    }

    /**
     * Get currencyFrom
     *
     * @return string
     */
    public function getCurrencyFrom()
    {
        return $this->currencyFrom;
    }

    /**
     * Set currencyTo
     *
     * @param string $currencyTo
     * @return TradeMessage
     */
    public function setCurrencyTo($currencyTo)
    {
        $this->currencyTo = $currencyTo;

        return $this;
    }

    /**
     * Get currencyTo
     *
     * @return string
     */
    public function getCurrencyTo()
    {
        return $this->currencyTo;
    }

    /**
     * Set amountSell
     *
     * @param string $amountSell
     * @return TradeMessage
     */
    public function setAmountSell($amountSell)
    {
        $this->amountSell = $amountSell;

        return $this;
    }

    /**
     * Get amountSell
     *
     * @return string
     */
    public function getAmountSell()
    {
        return $this->amountSell;
    }

    /**
     * Set amountBuy
     *
     * @param string $amountBuy
     * @return TradeMessage
     */
    public function setAmountBuy($amountBuy)
    {
        $this->amountBuy = $amountBuy;

        return $this;
    }

    /**
     * Get amountBuy
     *
     * @return string
     */
    public function getAmountBuy()
    {
        return $this->amountBuy;
    }

    /**
     * Set rate
     *
     * @param string $rate
     * @return TradeMessage
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set timePlaced
     *
     * @param \DateTime $timePlaced
     * @return TradeMessage
     */
    public function setTimePlaced($timePlaced)
    {
        $this->timePlaced = $timePlaced;

        return $this;
    }

    /**
     * Get timePlaced
     *
     * @return \DateTime
     */
    public function getTimePlaced()
    {
        return $this->timePlaced;
    }

    /**
     * Set originatingCountry
     *
     * @param string $originatingCountry
     * @return TradeMessage
     */
    public function setOriginatingCountry($originatingCountry)
    {
        $this->originatingCountry = $originatingCountry;

        return $this;
    }

    /**
     * Get originatingCountry
     *
     * @return string
     */
    public function getOriginatingCountry()
    {
        return $this->originatingCountry;
    }
}
