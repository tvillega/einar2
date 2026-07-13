#!/bin/bash

echo "-> ip net list"
ip net list

echo ":: Getting docker container names"

ALL_CONTAINERS=( $( docker ps --format "table {{ .Names }}") )

CONTAINERS=( )
for k in "${!ALL_CONTAINERS[@]}" ; do
  c="${ALL_CONTAINERS[$k]}"
  if [[ "${c:0:3}" == "e2-" ]] ; then
    CONTAINERS+=("$c")
    echo $c
  fi
done

echo ":: Getting docker containers PIDs"

PIDS=()
for k in "${!CONTAINERS[@]}" ; do
  P=( $(docker inspect --format '{{ .State.Pid }}' "${CONTAINERS[$k]}") )
  PIDS+=( $P )
  echo "$c -> $P"
done

echo ":: Detaching from docker network namespaces"

for k in "${!CONTAINERS[@]}" ; do
  ip netns delete "${CONTAINERS[$k]}" "${PIDS[$k]}"
done

echo "-> ip net list"
ip net list
