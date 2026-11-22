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
usage: einar2 <command>

Networking laboratory built on top of docker.

Commands:

  init  : installs or updates einar2 utilities, daemons, and images.
  labs  : manages docker compose laboratories and container sessions.
  netns : inspects and runs commands inside container network namespaces.

Run einar2 help <command> to read about a specific command
Authored by Tom Villegas (tvillega)
EOF
}

print_help_init() {
cat << 'EOF'
usage: einar2 init <command>

Fetch and install einar2 components and manage service lifecycle.

  <command> := selfupdate
             | utils
             | image

Subcommands:

  selfupdate : updates the utilities, the image and reloads the webui
  utils      : only downloads the utilities and installs them
  image      : only downloads the images and installs them

Examples:

  einar2 init utils
  einar2 init image
  einar2 init selfupdate
EOF
}

print_help_labs() {
cat << 'EOF'
usage: einar2 labs <command>

Manage docker compose laboratories and container shells.

  <command> := list all
             | open  <container-name>
             | start <lab>
             | stop  <lab>

Subcommands:

  list  : output all lab containers as JSON
  open  : open an interactive terminal inside <container-name>
  start : bring up <lab> from ${LABS_ROOT}/<lab>/docker-compose.yml
  stop  : tear down <lab> from ${LABS_ROOT}/<lab>/docker-compose.yml

  <lab> is a directory under ${LABS_ROOT} with the following structure:

       labname
       ├── docker-compose.yml
       └── docker-compose-networks.yml

Examples:

  einar2 labs start lab1
  einar2 labs list
  einar2 labs open e2-lab1-router
  einar2 labs stop lab1
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
  einar2 netns peek e2-web
  einar2 netns peek e2-router ip route
  einar2 netns peek e2-web tcpdump -i any -nn -w /tmp/e2-web.pcap
  einar2 netns peek e2-api ss -tulnp
  einar2 netns exec e2-web ls /sys/class/net
EOF
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        help)
            print_help
            exit 0
            ;;
        init)
            print_help_init
            exit 0
            ;;
        labs)
            print_help_labs
            exit 0
            ;;
        netns)
            print_help_netns
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
