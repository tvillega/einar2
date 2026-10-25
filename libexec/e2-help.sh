#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${BASH_SOURCE[0]} cannot be invoked directly" >&2
    exit 1
fi

print_help() {

cat << EOF
usage: einar2 [CMD]

Networking laboratory built on top of docker.

Commands:

  edit: modifies services and networks.
  gen : builds service and network blocks from archetypes.
  run : deploys labs. Requires superuser privileges.

Run ./einar2 <subcommand> to read about a specific subcommand.
Authored by Tom Villegas (tvillega)
EOF
}

print_help_edit() {

cat << EOF
usage: einar2 edit <section>

Edits the section of a laboratory.

  <section> := services <lab>
             | networks <lab>

  <lab> is a relative directory with the following structure:

       XX-labname
       ├── docker-compose.yml
       └── docker-compose-networks.yml

Examples are provided in the labs directory.
EOF
}

print_help_sync() {

cat <<EOF
usage: einar2 sync

Devel util. Sync configuration files to DOCUMENT_ROOT.

  <command> ::= yay
              | nay
EOF

}

print_help_run() {

cat <<EOF
usage: einar2 run <command>

Run a docker command. Requires a privileged user or sudo|doas.

  <command> := list   <section>
             | start  <lab>
             | stop   <lab>
             | shell  <lab>

  <section> := services
             | networks

  <lab> is a relative directory with the following structure:

       XX-labname
       ├── docker-compose.yml
       └── docker-compose-networks.yml
EOF
}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  help)
    print_help
    exit
    ;;
  edit)
    print_help_edit
    exit
    ;;
  sync)
    print_help_sync
    exit
    ;;
  run)
    print_help_run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
