#!/bin/bash

hook_run() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in config/hooks/*pre.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in config/hooks/*post.run ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_run_start() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in config/hooks/*pre.start ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in config/hooks/*post.start ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_run_stop() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in config/hooks/*pre.stop ; do
      chmod +x $a
      $a "${@}"  
      chmod -x $a
    done
  else  
    for a in config/hooks/*post.stop ; do
      chmod +x $a
      $a "${@}"  
      chmod -x $a
    done
  fi
 
}

hook_edit() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in config/hooks/*pre.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in config/hooks/*post.edit ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_gen() {

  local s="$1" ; shift

  if [[ $s -lt 0 ]] ; then
    for a in config/hooks/*pre.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  else
    for a in config/hooks/*post.gen ; do
      chmod +x $a
      $a "${@}"
      chmod -x $a
    done
  fi

}

hook_or_disable() {

  # Default hooks are mandatory for simplicity
  # removing any of them will disable all hooks

  [[ ! -d "config/hooks" ]] && exit
  [[ ! -f "config/hooks/00-default-post.edit"   ]] && exit
  [[ ! -f "config/hooks/00-default-post.gen"    ]] && exit
  [[ ! -f "config/hooks/00-default-post.run"    ]] && exit
  [[ ! -f "config/hooks/00-default-post.start"  ]] && exit
  [[ ! -f "config/hooks/00-default-post-stop"   ]] && exit
  [[ ! -f "config/hooks/00-default-pre.edit"    ]] && exit
  [[ ! -f "config/hooks/00-default-pre.gen"     ]] && exit
  [[ ! -f "config/hooks/00-default-pre.run"     ]] && exit
  [[ ! -f "config/hooks/00-default.pre.start"   ]] && exit
  [[ ! -f "config/hooks/00-default.pre-stop"    ]] && exit

}

hook_or_disable

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  run)
    shift
    hook_run "${@}"
    exit
    ;;
  start)
    shift
    hook_run_start "${@}"
    exit
    ;;
  stop)
    shift
    hook_run_stop "${@}"
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
