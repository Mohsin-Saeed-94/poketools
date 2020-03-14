#!/bin/bash
set -e

last_artifact_code=$(curl -IL "https://gitlab.com/${CI_PROJECT_NAMESPACE}/${CI_PROJECT_NAME}/-/jobs/artifacts/${CI_COMMIT_BRANCH}/download?job=data" 2>/dev/null | head -n 1 | cut -d$' ' -f2)
if [[ ${CI_COMMIT_MESSAGE} =~ ^.*\[data\] || ${last_artifact_code} -eq "404" ]]; then
  export PGPASSWORD=${POSTGRES_PASSWORD}
  apk add --no-cache postgresql-client
  cd /var/www
  composer install --prefer-dist --no-scripts --no-progress --no-suggest --no-interaction
  php bin/console doctrine:schema:update --force
  php -d memory_limit=-1 bin/console a2b:migrate --preserve
  cp resources/data/data_migration_map.sqlite "${CI_PROJECT_DIR}/"
  pg_dump --host=postgres --username="${POSTGRES_USER}" --dbname="${POSTGRES_DB}" --blobs --no-owner --no-privileges --format=custom --file="${CI_PROJECT_DIR}/template.generated.pgdump"
else
  echo "No data changes, using previous data set"
  curl "https://gitlab.com/${CI_PROJECT_NAMESPACE}/${CI_PROJECT_NAME}/-/jobs/artifacts/${CI_COMMIT_BRANCH}/raw/template.generated.pgdump?job=data" -o "${CI_PROJECT_DIR}/template.generated.pgdump"
  curl "https://gitlab.com/${CI_PROJECT_NAMESPACE}/${CI_PROJECT_NAME}/-/jobs/artifacts/${CI_COMMIT_BRANCH}/raw/data_migration_map.sqlite?job=data" -o "${CI_PROJECT_DIR}/data_migration_map.sqlite"
fi

exit 0
