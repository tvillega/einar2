#!/bin/bash

PKG_LIBX_DIR=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
PKG_ROOT_DIR=$( cd "$PKG_LIBX_DIR/.." ; pwd -P )
PKG_HOOK_DIR=$( cd "$PKG_ROOT_DIR/share/hooks" ; pwd -P )

hook_run() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$PKG_HOOK_DIR"/*pre.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$PKG_HOOK_DIR"/*post.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_edit() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$PKG_HOOK_DIR"/*pre.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$PKG_HOOK_DIR"/*post.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_gen() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$PKG_HOOK_DIR"/*pre.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$PKG_HOOK_DIR"/*post.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_or_disable() {

  # Default hooks are mandatory for simplicity
  # removing any of them will disable all hooks

  [[ ! -d "${PKG_HOOK_DIR}" ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-post.edit" ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-post.gen"  ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-post.run"  ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-pre.edit"  ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-pre.gen"   ]] && exit
  [[ ! -f "${PKG_HOOK_DIR}/00-default-pre.run"   ]] && exit

}

hook_or_disable

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  run)
    shift
    hook_run "${@}"
    exit
    ;;
  edit)
    shift
    hook_edit "${@}"
    exit
    ;;
  gen)
    shift
    hook_gen "${@}"
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
