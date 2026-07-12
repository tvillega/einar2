#!/bin/bash

## Finds the reserved namespace location /run/docker/netns
## and links it to the global /run/netns

echo "-> ip net list"
ip net list

echo ":: Gathering container names"
CONTAINERS=( $( docker ps --format "table {{ .Names }}") )
CONTAINERS=${C[@]:1} # Remove column name

for c in "${CONTAINERS[@]}" ; do
  echo $c
done

echo ":: Getting containers PIDs"

PIDS=()
for c in "${CONTAINERS[@]}" ; do
  P=( $(docker inspect --format '{{ .State.Pid }}' $c) )
  PIDS+=( $P )
  echo "$c -> $P"
done

echo ":: Hijacking network namespaces"
for c in "${CONTAINERS[@]}" ; do
  ip netns attach $c ${P[0]}
  P=${P[@]:1}
done

echo "-> ip net list"
ip net list
