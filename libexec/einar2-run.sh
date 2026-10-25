#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]##*/} cannot be invoked directly" >&2
    exit 1
fi

run_start()     {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run start <lab>"
    exit 1

  else
    docker compose -f "${1%%/}/docker-compose.yml" up
  fi
}

run_stop()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run stop <lab>"
    exit 1

  else
    docker compose -f "${1%%/}/docker-compose.yml" down
  fi
}

run_shell()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run shell <container>"
    exit 1

  else
    docker exec -it "$what" bash
  fi
}

run_list_services() {
  docker ps --format "table {{.Image}}\t{{.Names}}\t{{.Ports}}"
}

run_list_networks() {
  docker network ls --format "table {{.Driver}}\t{{.Name}}"
}

run_list()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run list services|networks"
    exit 1
  elif [[ "$what" == "services" ]] ; then
    run_list_services
    exit
  elif [[ "$what" == "networks" ]] ; then
    run_list_networks
    exit
  else
    run_list_services
    exit
  fi 

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  list)
    shift
    run_list "${@}"
    exit
    ;;
  shell)
    shift
    run_shell "${@}"
    exit
    ;;
  start)
    shift
    run_start "${@}"
    exit
    ;;
  stop)
    shift
    run_stop "${@}"
    exit
    ;;
  *)
    $PKG_LIB_DIR/e2-help.sh run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
