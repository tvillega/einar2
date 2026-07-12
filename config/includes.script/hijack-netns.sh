#!/bin/bash

## Finds the reserved namespace location /run/docker/netns
## and links it to the global /run/netns

echo "-> ip net list"
ip net list

echo ":: Gathering container names"
CONTAINERS=( $( docker ps --format "table {{ .Names }}") )
CONTAINERS=${CONTAINERS[@]:1} # Remove column name

for c in "${CONTAINERS[@]}" ; do
  echo $c
done

echo ":: Getting containers PIDs"

PIDS=()
for c in "${CONTAINERS[@]}" ; do
  if [[ "${c:0:3}" == "e2-" ]] ; then
    P=( $(docker inspect --format '{{ .State.Pid }}' $c) )
    PIDS+=( $P )
    echo "$c -> $P"
  fi
done

echo ":: Hijacking network namespaces"
for c in "${CONTAINERS[@]}" ; do
  ip netns attach $c ${PIDS[0]}
  PIDS=${PIDS[@]:1}
done

echo "-> ip net list"
ip net list
