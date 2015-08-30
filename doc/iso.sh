#!/bin/bash

# Basic script to grab a list of
# country and currency codes

ISO_3166='http://www.iso.org/iso/home/standards/country_codes/country_names_and_code_elements_txt-temp.htm'
ISO_4217='https://raw.githubusercontent.com/d4nyll/iso-helper/master/iso-4217-currencies/list.txt'

curl $ISO_3166 | awk -F';' 'NR>1 { print $2 }' | sort -u > iso-3166-country.txt
curl $ISO_4217 | awk '{ print $1 }' | sort -u > iso-4217-currency.txt

echo 'done', $?
