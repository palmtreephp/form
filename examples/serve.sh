#!/bin/bash

port=${1:-8080}

docker run --rm \
  -v $PWD/:/app -v $PWD/public:/app/examples/public -v $PWD/examples/tmp:/tmp \
  -p $port:$port \
  php:7.4-cli-alpine \
  php -S 0.0.0.0:$port -t /app/examples 2>&1 | sed 's/0.0.0.0/127.0.0.1/'
