#!/bin/bash

port=${1:-8080}

docker run --rm \
  -v "$PWD"/:/app -v "$PWD/public":/app/examples/public \
  -p "$port":"$port" \
  xisgo/php81-cli-xdebug:1.0.0 \
  php -c /app/examples/php.ini -S "0.0.0.0:$port" -t /app/examples 2>&1 | sed 's/0.0.0.0/127.0.0.1/'
