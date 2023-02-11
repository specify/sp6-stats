FROM php:8-fpm-alpine as back_end

LABEL maintainer="Specify Collections Consortium <github.com/specify>"

RUN docker-php-ext-install mysqli

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo -e \
  "\n\nmemory_limit = -1" \
  "\nmax_execution_time = 3600" \
  "\nbuffer-size=65535" \
  "\noutput_buffering=65535" \
  >>"$PHP_INI_DIR/php.ini"

WORKDIR /home/specify
RUN mkdir ./working-dir && chown -R www-data:www-data ./working-dir

USER www-data
