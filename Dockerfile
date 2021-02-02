#######################################
# BASE IMAGE
#######################################
FROM php:8.0-apache as base

WORKDIR /var/www

# Production PHP.ini
RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

# Install needed extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd intl opcache pcntl pdo_sqlite zip; \
    pecl install ds; \
    docker-php-ext-enable ds

# PHP configuration
COPY docker/app/entrypoint.sh /usr/local/bin/php-entrypoint
COPY docker/app/custom.ini $PHP_INI_DIR/conf.d/

# Web server configuration
COPY docker/app/app.apache2.conf ${APACHE_CONFDIR}/sites-available/app.conf
RUN a2ensite app; \
    a2dissite 000-default; \
    a2enmod rewrite

CMD ["/usr/local/bin/php-entrypoint"]

#######################################
# COMPOSER
#######################################
FROM base as build

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN rm -rf /var/www && mkdir /var/www
WORKDIR /var/www

COPY app/composer.* /var/www/
# Wacky dest path allows same composer.json path both locally and inside the container.
COPY a2b/ /var/a2b

ARG APP_ENV=prod

# Composer symlinking causes issues when these are copied later
RUN set -xe \
    && if [ "$APP_ENV" = "prod" ]; then export ARGS="--no-dev"; fi \
    && COMPOSER_MIRROR_PATH_REPOS=1 composer install --prefer-dist --no-scripts --no-progress --no-interaction $ARGS

# Remove assets from app image except those required for app function
COPY app/. /var/www
RUN set -xe \
    && rm -R /var/www/assets/*
COPY app/assets/static/map /var/www/assets/static/map

RUN composer dump-autoload --classmap-authoritative

#######################################
# ASSETS
#######################################
FROM node:12-alpine as webpack

ARG APP_ENV=prod

RUN apk add --no-cache git

RUN rm -rf /var/www && mkdir /var/www
WORKDIR /var/www

COPY app/public app/yarn.lock app/package.json app/webpack.config.js app/postcss.config.js /var/www/
COPY app/assets /var/www/assets
# Some assets come from PHP vendors
COPY --from=build /var/www/vendor/ /var/www/vendor/

RUN set -xe \
    && yarn install --non-interactive  --frozen-lockfile $ARGS

RUN set -xe \
    && mkdir -p public/build \
    && if [ "$APP_ENV" = "prod" ]; then export SCRIPT="build"; else export SCRIPT="dev"; fi \
    && yarn run $SCRIPT

# Cleanup sources to reduce image size
RUN set -xe \
    && rm -R /var/www/assets

#######################################
# APPLICATION
#######################################
FROM base as app

ARG APP_ENV=prod
ARG APP_DEBUG=0
ARG BUILD_NUMBER=debug

ENV APP_ENV $APP_ENV
ENV APP_DEBUG $APP_DEBUG
ENV SENTRY_DSN $SENTRY_DSN
ENV BUILD_NUMBER $BUILD_NUMBER

COPY --from=build /var/www/ /var/www/
COPY --from=webpack /var/www/public/build/manifest.json /var/www/public/build/manifest.json
COPY --from=webpack /var/www/public/build/entrypoints.json /var/www/public/build/entrypoints.json

RUN mkdir -p var/cache; \
    chown -R www-data:www-data var

#######################################
# DEVELOPMENT SUPPORT
#######################################
FROM app as app_dev

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install some helper utilities for server debugging
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends bash less nano psmisc

COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

# Install xdebug
RUN install-php-extensions xdebug
RUN { \
		echo 'xdebug.mode=debug'; \
		echo 'xdebug.discover_client_host=1'; \
	} >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini
