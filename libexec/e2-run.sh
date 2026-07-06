#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

run_start()     {
  docker-compose -f "${1%%/}/docker-compose-services.yml" up --build
}

run_stop()      {
  docker-compose -f "${1%%/}/docker-compose-services.yml" down
}

run_ls()        {
  docker ps
}

run_prune() {
  :
}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  start|up)
    shift
    run_start "${@}"
    exit
    ;;
  stop|down)
    shift
    run_stop "${@}"
    exit
    ;;
  ls|ps)
    shift
    run_ls "${@}"
    exit
    ;;
  prune)
    shift
    run_prune "${@}"
    exit
    ;;
  *)
    ./e2-help.sh run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
