#!/bin/bash

if [[ -z "$(ls -A /config)" ]]; then
  cp -r /config.default/* /config/
  chown -R lighttpd:lighttpd /config/www.d
  chown -R lighttpd:lighttpd /config/archetype.d
  chmod +x config/entrypoint.d/*.sh
fi

ln -sf /config/archetype.d /einar2/archetypes # because PHP reads raw filesystem

exec lighttpd -D -f /config.default/lighttpd.conf
