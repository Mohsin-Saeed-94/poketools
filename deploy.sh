#!/usr/bin/env bash

export ENVIRONMENT=${1}

# Create the destination directory
mkdir -p ./deploy/.generated

# Iterate through each template file to apply the template.
for f in ./deploy/tmpl/*.yaml
do
  # Skip db deployment template
  if [ "$(basename "$f")" != "db-deployment.yaml" ]
  then
    envsubst < "$f" > "./deploy/.generated/$(basename "$f")"
  fi
done

# Jobs must be deleted to retrigger them.
kubectl --namespace=poketools delete job --all
kubectl apply -f ./deploy/ -f ./deploy/.generated/
