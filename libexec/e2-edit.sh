#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

edit_services() {
  $EDITOR "${1%%/}/docker-compose-services.yml"
}

edit_networks() {
  $EDITOR "${1%%/}/docker-compose-networks.yml"
}


[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  services|s)
    shift
    edit_services "${@}"
    exit
    ;;
  networks|n)
    shift
    edit_networks "${@}"
    exit
    ;;
  *)
    ./e2-help.sh edit
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
