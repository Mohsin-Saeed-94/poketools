#!/bin/bash
set -e

docker info
docker login -u gitlab-ci-token -p "${CI_JOB_TOKEN}" "${CI_REGISTRY}"

if [[ ${CI_COMMIT_MESSAGE} =~ ^.*\[data\] ]]; then
  mv template.generated.pgdump docker/db/
  apk add --no-cache gcc libc-dev libffi-dev make openssl-dev py-pip python2-dev
  pip install docker-compose
  docker-compose build db
  docker image tag db "${IMAGE_BASENAME}/db:${CI_COMMIT_BRANCH}"
  docker push "${IMAGE_BASENAME}/db:${CI_COMMIT_BRANCH}"
else
  echo "No data changes, skipping data image build"
fi

docker image tag "${IMAGE_BASENAME}/db:${CI_COMMIT_BRANCH}" "${IMAGE_BASENAME}/db:${BUILD_NUMBER}"
docker push "${IMAGE_BASENAME}/db:${BUILD_NUMBER}"

exit 0
