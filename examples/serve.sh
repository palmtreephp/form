#!/bin/sh

docker build ./examples -t palmtree-form-examples && docker run --rm \
  -v $PWD/:/app -v $PWD/public:/app/examples/public \
  -p 8080:8080 \
  palmtree-form-examples \
  php -S 0.0.0.0:8080 -t /app/examples $@ 2>&1 | sed 's/0.0.0.0/127.0.0.1/'
