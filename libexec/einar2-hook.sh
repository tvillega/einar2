#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]##*/} cannot be invoked directly" >&2
    exit 1
fi

hook_run() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$PKG_HOOK_DIR"/*pre.run ; do
      $a "${@}"
    done
  else
    for a in "$PKG_HOOK_DIR"/*post.run ; do
      $a "${@}"
    done
  fi

}

hook_ipc() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$PKG_HOOK_DIR"/*pre.ipc ; do
      $a "${@}"
    done
  else
    for a in "$PKG_HOOK_DIR"/*post.ipc ; do
      $a "${@}"
    done
  fi

}

hook_or_disable() {

  # Default hooks are mandatory for simplicity
  # removing any of them will disable all hooks

  [[ -d "${PKG_HOOK_DIR}" ]] || exit
  [[ -f "${PKG_HOOK_DIR}/00-default-post.run"  ]] || exit
  [[ -f "${PKG_HOOK_DIR}/00-default-pre.run"   ]] || exit
  [[ -f "${PKG_HOOK_DIR}/00-default-post.ipc"  ]] || exit
  [[ -f "${PKG_HOOK_DIR}/00-default-pre.ipc"   ]] || exit

}

hook_or_disable

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  run)
    shift
    hook_run "${@}"
    exit
    ;;
  ipc)
    shift
    hook_ipc "${@}"
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
