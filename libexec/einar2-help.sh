#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

print_help() {
cat << 'EOF'
usage: einar2 [CMD]

Networking laboratory built on top of docker.

Commands:

  run   : deploys laboratories.
  netns : inspect and run commands inside container network namespaces.

Run einar2 <subcommand> to read about a specific subcommand.
Authored by Tom Villegas (tvillega)
EOF
}

print_help_netns() {
cat << 'EOF'
usage: einar2 netns <command>

Inspect and execute commands inside e2-* container network namespaces.

  <command> := attach
             | detach
             | list
             | exec <container> <cmd...>
             | peek <container> [nsenter-opts...] [cmd...]

Subcommands:

  attach : bind running e2-* container network namespaces to /run/netns/<name>
  detach : remove attached e2-* entries from /run/netns/
  list   : show active network namespaces on the host (lsns -t net)

  exec   : full namespace environment (ip netns exec)
           - remounts /sys for the container netns and isolates mounts/IPC
           - applies /etc/netns/<name>/ overrides and namespace hostname
           - best for testing as if inside an isolated host environment

  peek   : direct network namespace entry (nsenter --net=/run/netns/<name>)
           - switches only the network stack; keeps host /sys, /etc, and binaries
           - lets you run host tools (tcpdump, ss, curl, ip) on minimal containers
           - defaults to an interactive shell when [cmd...] is omitted
           - accepts extra nsenter flags before [cmd...] (e.g. --uts, --mount, --pid)

Examples:

  einar2 netns attach
  einar2 netns peek e2-router ip route show
  einar2 netns peek e2-server tcpdump -i any -nn -w /tmp/capture.pcap
  einar2 netns exec e2-server ls /sys/class/net
  einar2 netns detach
EOF
}

print_help_sync() {
cat << 'EOF'
usage: einar2 sync

Devel util. Sync configuration files to DOCUMENT_ROOT.

  <command> ::= yay
              | nay
EOF
}

print_help_run() {
cat << 'EOF'
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

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        help)
            print_help
            exit 0
            ;;
        netns)
            print_help_netns
            exit 0
            ;;
        sync)
            print_help_sync
            exit 0
            ;;
        run)
            print_help_run
            exit 0
            ;;
        *)
            break
            ;;
    esac
    shift
done

if [ "${1-}" = '--' ]; then
    shift
fi
