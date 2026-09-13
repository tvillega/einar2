#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]} cannot be invoked directly" >&2
    exit 1
fi

run_start()     {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run start <lab>"
    exit

  else
    docker compose -f "${1%%/}/docker-compose.yml" up
  fi
}

run_stop()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run stop <lab>"
    exit

  else
    docker compose -f "${1%%/}/docker-compose.yml" down
  fi
}

run_shell()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run shell <container>"
    exit

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
    exit
  elif [[ "$what" == "services" || "$what" == "service" || "$what" == "ls" ]] ; then
    run_list_services
    exit
  elif [[ "$what" == "networks" || "$what" == "network" ]] ; then
    run_list_networks
    exit
  else
    run_list_services
    exit
  fi 

}

run_ipc_listener() {

  local socket="/var/run/einar.socket"
  local daemon="${PKG_ROOT_DIR}/bin/einard"

  if [[ -S "$socket" ]] ; then
    echo "Closing previous session"
    rm -f "$socket"
  fi

  echo "Listening on socket $socket"
  socat UNIX-LISTEN:"$socket",fork,reuseaddr SYSTEM:"$daemon"

}

run_ipc_command() {

  local cmd="${1-}"
  local socket="/var/run/einar.socket"

  if [[ ! -S "$socket" ]] ; then
    echo "failed to connect to the einar API at unix://${socket}"
    exit
  fi

  printf -- "$cmd" | socat - UNIX-CONNECT:"$socket"

}

run_ipc() {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run ipc listener|<command>"
    exit
  elif [[ "$what" == "listener" ]] ; then
    run_ipc_listener
  else
    run_ipc_command "$what"
  fi

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  list)
    shift
    run_list "${@}"
    exit
    ;;
  list-networks|ln)
    shift
    run_list_networks
    exit
    ;;
  list-networks|ls)
    shift
    run_list_services
    exit
    ;;
  shell|sh)
    shift
    run_shell "${@}"
    exit
    ;;
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
  util)
    shift
    run_utility "${@}"
    exit
    ;;
  ipc)
    shift
    run_ipc "${@}"
    exit
    ;;
  *)
    $PKG_LIBX_DIR/e2-help.sh run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
