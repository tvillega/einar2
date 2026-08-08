#!/bin/bash

# Git doesn't like hardlinks
# during development archetypes and markdown files
# need to be synced with their counterparts inside www
# which simulate where they would be available
# inside the published Einar2 docker image

## SYNC ARCHETYPES

archdir="www/archetypes"

if [[ ! -d "$archdir" ]] ; then
  mkdir -v "$archdir"
fi

cp -rv --remove-destination archetypes/*  "${archdir}/"

## SYNC DOCS INDEX + CHANGELOG

cp -rv --remove-destination docs/index.md  www/docs/assets/index.md
cp -v  --remove-destination CHANGELOG.md   www/docs/assets/releasenotes.md

## SYNC USER GUIDE

guidedir="www/docs/assets/userguide"

if [[ ! -d "$guidedir" ]] ; then
  mkdir -v "$guidedir"
fi

cp -rv --remove-destination docs/userguide/* "${guidedir}/"

## SYNC ADVANCED USER GUIDE

advguidedir="www/docs/assets/advguide"

if [[ ! -d "$advguidedir" ]] ; then
  mkdir -v "$advguidedir"
fi

cp -rv --remove-destination docs/advguide/* "${advguidedir}/"

## SYNC SETTINGS

cp -v  --remove-destination config/settings.json www/settings.json
