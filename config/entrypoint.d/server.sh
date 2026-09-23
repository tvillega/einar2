#!/bin/bash

CURRENT_GW=$(ip route show default | awk '{ print $3 }')

if [[ "$CURRENT_GW" != "$DEFAULT_GW" ]] ; then
  ip route del default
  ip route add default via $DEFAULT_GATEWAY
fi

exec lighttpd -D -f /config/lighttpd.d/server.conf
