#!/bin/bash

echo 'Running symfony dev server on port 8000...'

# Ensure script is executed from project root

[[ ! -x app/console ]] && {
  echo 'Error: please execute this script from the project root. Exiting.';
  exit 1;
}

# Serve

app/console server:run -vvv
