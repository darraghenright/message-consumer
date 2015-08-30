#!/bin/bash

echo 'Initialising symfony application for dev '

# Ensure script is executed from project root

[[ ! -x app/console ]] && {
  echo 'Error: please execute this script from the project root. Exiting.';
  exit 1;
}

# Confirm destruction

echo -n 'This operation will destroy all existing data. Continue? [y/n]: '
read FOO

if [[ 'y' = $FOO ]]
then
  app/console doctrine:database:drop --force
  app/console doctrine:database:create
  app/console doctrine:schema:create
else
  echo 'Initialisation cancelled. Exiting.'
fi
