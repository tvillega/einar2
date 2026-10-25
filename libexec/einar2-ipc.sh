#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]##*/} cannot be invoked directly" >&2
    exit 1
fi

ipc_listener() {

  local socket="/var/run/einar.socket"
  local daemon="${PKG_ROOT_DIR}/bin/einard"

  if [[ -S "$socket" ]] ; then
    echo "Closing previous session"
    rm -f "$socket"
  fi

  echo "Listening on socket $socket"
  socat UNIX-LISTEN:"$socket",fork,reuseaddr SYSTEM:"$daemon"

}

ipc_command() {

  local cmd="${1-}"
  local socket="/var/run/einar.socket"

  if [[ ! -S "$socket" ]] ; then
    echo "failed to connect to the einar API at unix://${socket}"
    exit
  fi

  printf -- "$cmd" | socat - UNIX-CONNECT:"$socket"

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  listener)
    shift
    ipc_listener "${@}"
    exit
    ;;
  *)
    shift
    ipc_command "${@}"
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
