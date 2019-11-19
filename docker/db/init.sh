#!/usr/bin/env sh

pg_restore --username="${POSTGRES_USER}" --dbname="${POSTGRES_DB}" --exit-on-error --no-owner --no-privileges "/initdb.d/template.generated.pgdump"
