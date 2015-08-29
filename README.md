# Message Consumer

* Author: Darragh Enright <darraghenright@gmail.com>
* Date: 2015-08-29

## Specification

As a component of the **Market Trade Processor**, the **Message Consumer** provides a HTTP endpoint to consume trade messages.

Messages are submitted to the Message Consumer via method `POST` to the endpoint `/trade/message/`.

The endpoint accepts anonymous `application/json` requests in the following example format:

```
{
    "userId": "134256",
    "currencyFrom": "EUR",
    "currencyTo": "GBP",
    "amountSell": 1000,
    "amountBuy": 747.10,
    "rate": 0.7471,
    "timePlaced": "24­JAN­15 10:27:44",
    "originatingCountry": "FR"
}
```
