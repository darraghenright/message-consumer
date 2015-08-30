#!/bin/bash

echo 'Running unit tests...'

# Ensure phpunit is installed

[[ ! -x `which phpunit` ]] && {
  echo 'Error: cannot find phpunit in the current path. Exiting.';
  exit 1;
}

# Ensure script is executed from project root

[[ ! -d app ]] && {
  echo 'Error: please execute this script from the project root. Exiting.';
  exit 1;
}

# Run tests in src (using phpunit.xml.dist config in app)

phpunit -c app/ src/
