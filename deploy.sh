#!/usr/bin/env bash

export ENVIRONMENT=${1}

# Create the destination directory
mkdir -p ./deploy/.generated

# Iterate through each template file to apply the template.
for f in ./deploy/tmpl/*.yaml
do
  envsubst < $f > "./deploy/.generated/$(basename $f)"
done

# Jobs must be deleted to retrigger them.
kubectl delete jobs --all
kubectl apply -f ./deploy/ -f ./deploy/.generated/
