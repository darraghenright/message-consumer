#!/bin/bash

# API sanity-check with curl

read -r -d '' DATA <<EOF
{
  "userId":"134256",
  "currencyFrom":"EUR",
  "currencyTo":"GBP",
  "amountSell":1000,
  "amountBuy":747.1,
  "rate":0.7471,
  "timePlaced":"24-JAN-15 10:27:44",
  "originatingCountry":"FR"
}
EOF

curl -iv :8000/trade/message/ \
     -H 'Content-Type: application/json' \
     -d "$DATA"
