#!/bin/bash

# Git doesn't like hardlinks
# during development archetypes and markdown files
# need to be synced with their counterparts inside www
# which simulate where they would be available
# inside the published Einar2 docker image

if [[ ! -d www/archetypes ]] ; then
  mkdir -v www/archetypes
fi

cp -rv --remove-destination archetypes/*    www/archetypes/

if [[ ! -d www/docs/md ]] ; then
  mkdir -v www/docs/md
fi

cp -rv --remove-destination docs/*          www/docs/md/
cp -v  --remove-destination CHANGELOG.md  www/docs/md/releasenotes.md
