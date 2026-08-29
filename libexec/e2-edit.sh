#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

LIBX="${E2_LIBEXEC-./libexec}"

check_dir() {

  local wht="${1-}"
  local lab="${2-}"

  if [[ -z "$lab" ]] ; then
    echo "usage: edit $wht <lab>"
    exit
  elif [[ ! -d "$lab" ]] ; then
    echo "Laboratory $lab not found"
    exit
  elif [[ "$lab" == "labs" ]] ; then
    echo "Can't use root directory as laboratory"
    exit
  fi  

}

edit_services() {
  check_dir "services" "${1-}"
  $EDITOR "${1%%/}/docker-compose.yml"
}

edit_networks() {
  check_dir "networks" "${1-}"
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
    $LIBX/e2-help.sh edit
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
