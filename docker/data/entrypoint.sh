#!/usr/bin/env sh

export PGPASSWORD=${POSTGRES_PASSWORD}

function executeSql() {
psql -e --host=${POSTGRES_HOST} --port=${POSTGRES_PORT} --username=${POSTGRES_USER} --dbname=${POSTGRES_DB} --file="${1}"
}

executeSql "/working/purge.pg.sql"
executeSql "/working/template.generated.pg.sql"

echo "Completed loading data."

return 0
