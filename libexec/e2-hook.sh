#!/bin/bash

LIBX="${E2_LIBEXEC-./libexec}"
HOOKS_DIR="${E2_HOOKS-./share/hooks}"

hook_run() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$HOOKS_DIR"/*pre.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$HOOKS_DIR"/*post.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_edit() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$HOOKS_DIR"/*pre.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$HOOKS_DIR"/*post.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_gen() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in "$HOOKS_DIR"/*pre.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in "$HOOKS_DIR"/*post.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_or_disable() {

  # Default hooks are mandatory for simplicity
  # removing any of them will disable all hooks

  [[ ! -d "${HOOKS_DIR}" ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-post.edit" ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-post.gen"  ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-post.run"  ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-pre.edit"  ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-pre.gen"   ]] && exit
  [[ ! -f "${HOOKS_DIR}/00-default-pre.run"   ]] && exit

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
