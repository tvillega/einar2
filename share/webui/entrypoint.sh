#!/bin/sh

sed -i 's|jailbreak": true|jailbreak": false|' /einar2/webui.json

exec lighttpd -D -f /einar2/lighttpd.conf
