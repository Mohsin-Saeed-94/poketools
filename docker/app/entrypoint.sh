#!/usr/bin/env sh

chown -R www-data:www-data var
php -d memory_limit=256M bin/console cache:clear
bin/console assets:install

apache2-foreground
