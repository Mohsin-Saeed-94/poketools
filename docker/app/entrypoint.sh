#!/usr/bin/env sh

php -d memory_limit=256M bin/console cache:clear
chown -R www-data:www-data var

php-fpm
