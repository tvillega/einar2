#!/bin/bash

echo "WARNING: Einar2 is jailbreaked and has shell access to your host system"

sed -i 's|jailbreak": false|jailbreak": true|' /einar2/defaults.json

ln -sf /config /einar2/config

exec lighttpd -D -f /einar2/lighttpd.conf
