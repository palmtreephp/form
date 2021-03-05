FROM alpine:3.13

RUN apk add --no-cache --update \
    curl \
    php7 \
    php7-curl \
    php7-fpm \
    php7-json \
    php7-ctype \
    php7-mbstring \
    php7-xmlwriter \
    php7-pecl-xdebug && \
    echo "zend_extension=xdebug.so" >> /etc/php7/conf.d/99_xdebug.ini
