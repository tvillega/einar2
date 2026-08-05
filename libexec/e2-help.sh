#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

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

print_help_gen() {

cat << EOF
usage: einar2 gen <archetype>

Prints a service or network block from an archetype.

  <archetype> := network  <network> <mask> <gateway>
               | compose  <section>
               | computer <name>
               | join-network <address> <network>
               | laboratory <name>
               | router   <name>
               | server   <name>

  <section> := services
             | networks

  <address> is an assignable IPv4 for a service,
            excludes reserved numbers x.x.x.{0,1,255}.
  <network> is an IPv4 address that identifies a whole network,
            commonly assigned to x.x.x.0 for /24 masks.
  <mask>    is the network mask, ranging from 0 to 32,
            leading slash is ignored if present.
  <gateway> is an IPv4 address that acts as gateway,
                 the x.x.x.1 corresponds to docker's guest:host bind.

  <section> fills compose with Einar2 custom images and networks
            to use as base for new laboratories

Archetypes follow Einar naming conventions, and prepend e2
to container's names for clearer reverse DNS lookup outputs.
EOF
}

print_help_run() {

cat <<EOF
usage: einar2 run <command>

Run a docker command. Requires a privileged user or sudo|doas.

  <command> := list   <section>
             | shell  <lab>
             | start  <lab>
             | stop   <lab>
             | shell  <lab>
             | util   <utility>

  <section> := services
             | networks

  <lab> is a relative directory with the following structure:

       XX-labname
       ├── docker-compose.yml
       └── docker-compose-networks.yml

  <utility> is a script under the utils directory, you must
            reference it by name without the .sh extension.

            You can create your own utilities and save them
            under the utils dir to call them from einar2.
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
  gen)
    print_help_gen
    exit
    ;;
  run)
    print_help_run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
