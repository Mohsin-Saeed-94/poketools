#!/usr/bin/env sh

php -d memory_limit=256M bin/console cache:clear
bin/console assets:install

php-fpm
