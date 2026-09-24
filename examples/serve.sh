#!/bin/bash

port=${1:-8080}

docker run --rm \
  -v "$PWD"/:/app -v "$PWD/dist":/app/examples/dist \
  -p "$port":"$port" \
  php:8.5-cli \
  php -c /app/examples/php.ini -S "0.0.0.0:$port" -t /app/examples 2>&1 | sed 's/0.0.0.0/localhost/'
