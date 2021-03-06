# Message Consumer

* Author: Darragh Enright <darraghenright@gmail.com>
* Date: 2015-08-29

## Table of Contents

* Description
  * Introduction
* Message Lifecycle
  * Request
    * Rate Limiting
  * Response
    * Summary
* Message Structure and Data
  * Structure Validation
  * Data Validation
    * Fields
    * Data Integrity
  * Data Transformation
* Implementation
  * Development
  * Testing
  * Deployment

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

#### Rate Limiting

A simple implementation of the "leaky bucket" strategy facilitates rate limiting in the application. Access tokens are stored with a ttl in a small Redis ElastiCache cluster.

A `RateLimitVoter` security voter is registered as a service. When a `POST` request to `/trade/message` is received, this service intercepts the request, and using the `X-FORWARDED-FOR` header specified by AWS's Elastic Load Balancer. It performs a `KEY` glob for similar keys. If the number of aggregated matches returned exceeds a configured maximum, the request is denied with a `500 Internal Server Error`.

The following environmental variables are injected into the Elastic Beanstalk production environment. This is specified in `composer.json` under `extra.incenteev-parameters.env-map`:

```
"redis_dsn": "REDIS_DSN",
"ratelimit_max": "RATELIMIT_MAX",
"ratelimit_ttl": "RATELIMIT_TTL"
```

> Currently the production environment is configured with a `ratelimit_max` of **5** connections/tokens, with a `ratelimit_ttl` of **5** seconds.

For more details see:

* `src/AppBundle/Security/Voter/RateLimitVoter.php` for implementation
* `app/config/security`

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

## Implementation

### Development

The application is built in PHP using Symfony2 version `2.7.*`. The latest version of PHP is recommended, and some features require >= `5.4`. Once the repository is cloned, run `composer update`. You will be prompted for the following configuration parameters:

* `database_host`
* `database_port`
* `database_name`
* `database_user`

Once complete, run the following command to initialise the application database and schema:

```
./bin/init.sh
```

The application can be served locally in development mode on the command line from the project root as follows:

```
./bin/serve.sh
```

You can now issue an ad-hoc request to ensure that everything is working correctly. The following command issues a `POST` request via `curl` with an example JSON payload to the endpoint `/trade/message/` on port `8000`:

```
./bin/request.sh
```

A basic fixtures script may be run to populate the database with random `TradeMessage` records. The fixtures file is located at `src/AppBundle/DataFixtures/ORM/LoadTradeMessageFixtures.php`. The script can be run in the terminal from the project root as follows:

```
app/console doctrine:fixtures:load
```

### Testing

A suite of unit and functional tests accompany the implementation. Tests can be found in directory `src/AppBundle/Tests/`. Tests can be run on the command line from the project root as follows:

```
./bin/test.sh
```

### Deployment

The Message Consumer can be deployed to AWS Elastic Beanstalk using the Elastic Beanstalk Command Line Interface (EB CLI).

Deployment actions are configured in `.ebextensions/message-consumer.config`. On deployment, this script will run deployment commands and hooks, and configure environmental variables.

#### Init

Follow the steps to initialise the application for deployment:

* Run `eb init` (with optional `--profile eb-myprofilename`)
* Select a default region; e.g: `eu-west-1` (Ireland)
* Enter Application Name; e.g: `consumer`
* Select a platform; e.g: `PHP`
* Select a platform version; e.g: `PHP 5.6`
* Do you want to set up SSH for your instances? `y`
* Type a keypair name; e.g: `mykeypairname` (with optional passphrase)

A configuration should now be located at `.elasticbeanstalk/config.yml`

#### Create

Next, create an environment and deploy the application:

* `eb create --database` to create with RDS instance
* Enter Environment Name; e.g: `consumer-dev`
* Enter DNS CNAME prefix; e.g: `consumer-dev`
* Enter an RDS DB username
* Enter an RDS DB master password

The process will then begin - the latest commit is zipped and uploaded and the application
will be bootstrapped. This will take a few minutes.

RDS DB parameters are automatically injected into the environment as `RDS_*` environment variables. The application transparently uses these values automatically if present. This is specified in `composer.json` under `extra.incenteev-parameters.env-map`:

```
"incenteev-parameters": {
    "file": "app/config/parameters.yml",
    "env-map": {
        "database_host": "RDS_HOSTNAME",
        "database_port": "RDS_PORT",
        "database_name": "RDS_DB_NAME",
        "database_user": "RDS_USERNAME",
        "database_password": "RDS_PASSWORD",
        "secret": "APP_SECRET",
        "redis_dsn": "REDIS_DSN",
        "ratelimit_max": "RATELIMIT_MAX",
        "ratelimit_ttl": "RATELIMIT_TTL"
    }
}
```

Once Elastic Beanstalk finishes uploading, building and configuring the environment, the application should be live.

To review logs in the event of an error, run `eb logs`.

### Update

To update committed changes use `eb deploy`.
