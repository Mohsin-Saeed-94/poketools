#!/bin/bash
set -xe

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/bin/composer
export PGPASSWORD=${POSTGRES_PASSWORD}
apk add --no-cache postgresql-client
cd /var/www
composer install --prefer-dist --no-scripts --no-progress --no-suggest --no-interaction
php bin/console doctrine:schema:update --force
php -d memory_limit=-1 bin/console a2b:migrate --preserve
pg_dump --host="${POSTGRES_HOST}" --username="${POSTGRES_USER}" --dbname="${POSTGRES_DB}" --blobs --no-owner --no-privileges --format=custom --file="template.generated.pgdump"

exit 0
