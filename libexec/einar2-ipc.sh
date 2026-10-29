#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]##*/} cannot be invoked directly" >&2
    exit 1
fi

XDG_RUNTIME_DIR="${XDG_RUNTIME_DIR:-/tmp}"
SOCKET="${XDG_RUNTIME_DIR}/einar2.sock"

ipc_listener() {

  local daemon="${PKG_BIN_DIR}/einard"

  if [[ -S "$SOCKET" ]] ; then
    echo "Closing previous session"
    rm -f "$SOCKET"
  fi

  echo "Listening on socket $SOCKET"
  socat UNIX-LISTEN:"$SOCKET",fork,reuseaddr SYSTEM:"$daemon"

}

ipc_command() {

  local cmd="${1-}"

  if [[ ! -S "$SOCKET" ]] ; then
    echo "failed to connect to the einar API at unix://${SOCKET}"
    exit
  fi

  printf -- "$cmd" | socat - UNIX-CONNECT:"$SOCKET"

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  listener)
    shift
    ipc_listener "${@}"
    exit
    ;;
  do)
    shift
    ipc_command "${@}"
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
