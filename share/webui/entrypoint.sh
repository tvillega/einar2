#!/bin/bash

if [[ -z "$(ls -A /config)" ]]; then
  cp -r /config.default/* /config/
fi

ln -sf /config /einar2/config

exec lighttpd -D -f /einar2/lighttpd.conf
