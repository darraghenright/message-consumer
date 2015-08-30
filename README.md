# Message Consumer

* Author: Darragh Enright <darraghenright@gmail.com>
* Date: 2015-08-29

## Description

### Introduction

As a component of the **Market Trade Processor**, the **Message Consumer** provides a HTTP endpoint to consume **Trade Message** data.

A Trade Message are submitted to the Message Consumer via method `POST` to the endpoint `/trade/message/`.

## Message Lifecycle

A HTTP request containing a Trade Message is received and handled by the Message Consumer, and an appropriate HTTP response is generated and returned to the originating client.

The following diagram documents the possible outcomes of a single HTTP request:

![Message Consumer request flow](doc/assets/message-consumer-request-flow.png)

### Request

Trade Message data comprises the `POST` payload of the HTTP request received by the Message Consumer. A valid request:

* is received via method `POST`
* may be anonymous (`Authorization` is not required in the feature spec)
* includes `Content-Type` header `application/json`
* contains a well-formed JSON payload
* contains a JSON payload that validates to an expected format

### Response

The Message Consumer handles the request and responds with an appropriate HTTP response as per the documented flow. The requesting client is expected to determine the outcome from the response status code; e.g: `201 Created`.

Additionally, each response is of type `application/json` and contains a `message` body that describes the outcome in greater detail; e.g:

````
{
    "message": "Syntax error, malformed JSON"
}
````

The above example would accompany a HTTP Status Code of `400 Bad Request`, in which a Trade Message could not be parsed due to incorrect format.

#### Summary 

A summary of all expected HTTP Response Codes returned by the Message Consumer component is as follows:

* `405 Method Not Allowed` for all requests that are not method `POST`
* `400 Bad Request` if Content Type is incorrect
* `400 Bad Request` if JSON payload is malformed and cannot be parsed
* `422 Unprocessable Entity` if JSON payload does not validate to expected structure
* `503 Service Unavailable` for persistence issues; e.g: database outage
* `201 Created` for successful persistence

Status codes indicating an error on the server/infrastructure level are out of scope for this document; e.g `500 Internal Server Error` or `503 Service Unavailable` indicating that the endpoint's environment is misconfigured of offline.

## Message Structure and Data

### Structure Validation

A request containing a valid Trade Message is a well-formed JSON payload that validates to an expected format. An example JSON payload is as follows:

```
{
    "userId": "134256",
    "currencyFrom": "EUR",
    "currencyTo": "GBP",
    "amountSell": 1000,
    "amountBuy": 747.10,
    "rate": 0.7471,
    "timePlaced": "24-JAN-15 10:27:44",
    "originatingCountry": "FR"
}
```

A JSON payload that does not strictly comply to the structure defined returns a HTTP Status Code of `422 Unprocessable Entity`. Extra fields are filtered, and missing fields are flagged by data validation.

### Data Validation

#### Fields

Values for all fields are required. The JSON data provided is considered invalid if any field is blank. Based on the example JSON payload provided, valid data for each field is:

* `userId` - `string`
* `currencyFrom` - `string` corresponding to 3-digit ISO 4217 Currency Code
* `currencyTo` - `string` corresponding to 3-digit ISO 4217 Currency Code
* `amountSell` - unsigned numeric value (`float` or `integer`)
* `amountBuy` - unsigned numeric value (`float` or `integer`)
* `rate` - unsigned numeric value (`float` or `integer`)
* `timePlaced` - type `string` corresponding to date format `j-M-y H:i:s`
* `originatingCountry` - type `string` corresponding to 2-digit ISO 3166-1 Alpha-2 Country Code

#### Data Integrity

Validation will be performed to cross-check and ensure the integrity of the data contained in these fields where possible, including:

1. The values of `rate`, `amountSell` and `amountBuy` are inter-related. `amountBuy` is the product of `amountSell` and `rate`; i.e: `amountSell * rate = amountBuy`.

2. All times are assumed to be UTC. The value for `timePlaced` should not be greater than the time the request is received. 

3. The values for `currencyFrom` and `currencyTo` should not match.

*ISO Code Data Sources:*

* [ISO 4217 Currency Codes (.csv)](http://data.okfn.org/data/core/currency-codes/r/codes-all.csv)
* [ISO 3166-1 Alpha-2 Country Codes (.csv)](http://data.okfn.org/data/core/country-codes/r/country-codes.csv)

### Data Transformation

The following data will be transformed prior to data persistence:

* `timePlaced` will be reformatted according to MySQL's `datetime` format; i.e: `Y-m-d H:i:s`
* `amountSell` and `amountBuy` will be converted to `float`

*Note*: The values of `amountSell` and `amountBuy` may also be converted to their centesimal representation, assuming that each value received is a monetary unit (USD, EUR, GBP) with 1/100th subdivisions, (cents or pence) and stored internally as type `integer`.

However, the requirement specification does not currently specify the currencies the Message Consumer expects to receive, and since not all currencies are centesimal (e.g Japanese Yen), values will not currently be transformed in this manner.
