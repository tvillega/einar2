#!/bin/bash

hook_run() {

  if [[ $1 -lt 0 ]] ; then
    for a in config/hooks/*pre.run ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  else
    for a in config/hooks/*post.run ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  fi

}

hook_edit() {

  if [[ $1 -lt 0 ]] ; then
    for a in config/hooks/*pre.edit ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  else
    for a in config/hooks/*post.edit ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  fi

}

hook_gen() {

  if [[ $1 -lt 0 ]] ; then
    for a in config/hooks/*pre.gen ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  else
    for a in config/hooks/*post.gen ; do
      chmod +x $a
      bash $a
      chmod -x $a
    done
  fi

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  run)
    shift
    hook_run $2
    exit
    ;;
  edit)
    shift
    hook_edit $2
    exit
    ;;
  gen)
    hook_gen $2
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
