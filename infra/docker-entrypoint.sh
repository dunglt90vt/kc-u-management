#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  /usr/bin/composer install --prefer-dist --no-progress --no-interaction
fi

exec docker-php-entrypoint "$@"
