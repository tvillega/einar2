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

## SYNC DOCS (ROOT)

docsdir="www/docs/md"

if [[ ! -d "$docsdir" ]] ; then
  mkdir -v "$docsdir"
fi

cp -rv --remove-destination docs/index.md  "${docsdir}/index.md"
cp -v  --remove-destination CHANGELOG.md   "${docsdir}/releasenotes.md"

## SYNC USER GUIDE

guidedir="www/docs/userguide/md"

if [[ ! -d "$guidedir" ]] ; then
  mkdir -v "$guidedir"
fi

cp -rv --remove-destination docs/userguide/* "${guidedir}/"

## SYNC ADVANCED USER GUIDE

advguidedir="www/docs/advguide/md"

if [[ ! -d "$advguidedir" ]] ; then
  mkdir -v "$advguidedir"
fi

cp -rv --remove-destination docs/advguide/* "${advguidedir}/"

## SYNC CONTACT

contactdir="www/docs/contact/md"

if [[ ! -d "$contactdir" ]] ; then
  mkdir -v "$contactdir"
fi

cp -rv --remove-destination docs/contact/* "${contactdir}/"

## SYNC DOWNLOAD

downloaddir="www/docs/download/md"

if [[ ! -d "$downloaddir" ]] ; then
  mkdir -v "$downloaddir"
fi

cp -rv --remove-destination docs/download/* "${downloaddir}/"

## SYNC LINKS

linksdir="www/docs/links/md"

if [[ ! -d "$linksdir" ]] ; then
  mkdir -v "$linksdir"
fi

cp -rv --remove-destination docs/links/* "${linksdir}/"

## SYNC SETTINGS

cp -v  --remove-destination config/settings.json www/settings.json
