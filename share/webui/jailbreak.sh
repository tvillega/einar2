#!/bin/bash

echo "WARNING: Einar2 is jailbreaked and has shell access to your host system"

sed -i 's|jailbreak": false|jailbreak": true|' /einar2/defaults.json

if [[ -z "$(ls -A /config)" ]]; then
  cp -r /config.default/* /config/
fi

ln -sf /config /einar2/config

exec lighttpd -D -f /einar2/lighttpd.conf
