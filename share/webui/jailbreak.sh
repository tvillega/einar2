#!/bin/sh

set -eu
IFS='
	'

echo "WARNING: Einar2 is jailbroken and has shell access to your host system"

exec lighttpd -D -f /einar2/lighttpd.conf
