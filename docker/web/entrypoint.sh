#!/usr/bin/env sh

# Apply configuration
envsubst '${NGINX_BACKEND_HOST}' \
    < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

nginx -g "daemon off;"
