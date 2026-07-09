#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

init_registry() {
  docker-compose -f "../compose/docker-compose-registry.yml" up -d
}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  registry|r)
    shift
    init_repository "${@}"
    exit
    ;;
  *)
    ./libexec/e2-help.sh init
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi



