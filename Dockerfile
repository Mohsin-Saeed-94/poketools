#######################################
# BASE IMAGE
#######################################
FROM php:7.3-fpm-alpine as base

WORKDIR /var/www

# Install dependencies
RUN set -xe \
    && apk add --no-cache bash icu-dev libgd libjpeg libpng-dev libzip-dev postgresql-dev \
    && docker-php-ext-install gd intl opcache pcntl pdo_pgsql zip

COPY docker/app/entrypoint.sh /usr/local/bin/php-entrypoint

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/app/custom.ini $PHP_INI_DIR/conf.d/

CMD ["/usr/local/bin/php-entrypoint"]

#######################################
# COMPOSER
#######################################
FROM base as build

COPY --from=composer:1.8 /usr/bin/composer /usr/local/bin/composer

RUN rm -rf /var/www && mkdir /var/www
WORKDIR /var/www

COPY app/composer.* /var/www/

ARG APP_ENV=prod

RUN set -xe \
    && if [ "$APP_ENV" = "prod" ]; then export ARGS="--no-dev"; fi \
    && composer install --prefer-dist --no-scripts --no-progress --no-suggest --no-interaction $ARGS

COPY app/. /var/www

RUN composer dump-autoload --classmap-authoritative

#######################################
# ASSETS
#######################################
FROM node:10.15-alpine as webpack

ARG APP_ENV=prod

RUN rm -rf /var/www && mkdir /var/www
WORKDIR /var/www

COPY app/public app/yarn.lock app/package.json app/webpack.config.js /var/www/
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
# WEB SERVER
#######################################
FROM nginx:1.15-alpine as web

ARG NGINX_BACKEND_HOST=app
ENV NGINX_BACKEND_HOST $NGINX_BACKEND_HOST

WORKDIR /var/www/public

COPY docker/web/entrypoint.sh /usr/local/bin/nginx-entrypoint

COPY docker/web/default.conf /etc/nginx/conf.d/default.conf.tmpl

COPY --from=webpack /var/www/public /var/www/public

CMD ["/usr/local/bin/nginx-entrypoint"]

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
COPY --from=webpack /var/www/public/build /var/www/public/build

RUN mkdir -p var/cache \
    && chown -R www-data:www-data var

#######################################
# APP DEVELOPMENT SUPPORT
#######################################
FROM app as app_dev

COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

RUN set -xe \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.7.1 \
    && docker-php-ext-enable xdebug

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

VOLUME /var/www

#######################################
# WEB DEVELOPMENT SUPPORT
#######################################
FROM web as web_dev

VOLUME /var/www
