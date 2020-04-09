#!/bin/bash
set -xe

if [[ ${CI_COMMIT_MESSAGE} =~ ^.*\[data\] ]]; then
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
  mv composer.phar /usr/bin/composer
  cd /var/www
  composer install --prefer-dist --no-progress --no-suggest --no-interaction
  php -d memory_limit=-1 bin/phpunit --configuration=phpunit.xml.dist --testsuite="Data Schema"
else
  echo "No data changes, skipping data test"
fi

exit 0
