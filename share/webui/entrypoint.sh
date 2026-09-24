#!/bin/bash

if [[ -z "$(ls -A /config)" ]]; then
  cp -r /config.default/* /config/
fi

exec lighttpd -D -f /einar2/lighttpd.conf
