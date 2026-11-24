#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

netns_attach_one() {
    c="$1"

    if [ -e "/run/netns/$c" ]; then
        echo "Skipping $c (already attached)"
        return 0
    fi

    P=$(docker inspect --format '{{ .State.Pid }}' "$c" 2>/dev/null)
    if [ -n "$P" ] && [ "$P" != "0" ]; then
        echo "$c -> $P"
        ip netns attach "$c" "$P"
    else
        echo "Warning: Could not get valid PID for $c" >&2
        return 1
    fi
}

netns_detach_one() {
    c="$1"

    if [ -e "/run/netns/$c" ]; then
        echo "Detaching $c"
        ip netns delete "$c" 2>/dev/null || true
    else
        echo "Skipping $c (not attached)"
    fi
}

netns_attach() {
    if [ "$#" -gt 0 ]; then
        netns_attach_one "$1"
        return $?
    fi

    echo "-> ip net list"
    ip net list

    echo ":: Attaching einar2 docker containers"

    for c in $(docker ps --format '{{.Names}}'); do
        case "$c" in
            e2-*)
                netns_attach_one "$c" || true
                ;;
        esac
    done

    echo "-> ip net list"
    ip net list
}

netns_detach() {
    if [ "$#" -gt 0 ]; then
        netns_detach_one "$1"
        return $?
    fi

    echo "-> ip net list"
    ip net list

    echo ":: Detaching einar2 docker containers"

    for ns_path in /run/netns/e2-*; do
        [ -e "$ns_path" ] || continue
        c="${ns_path##*/}"
        netns_detach_one "$c"
    done

    echo "-> ip net list"
    ip net list
}

netns_list() {
    echo "-> lsns -t net"
    lsns -t net
}

netns_exec() {
    c="${1:-}"
    if [ -z "$c" ]; then
        echo "usage: netns exec <container-name> <command...>" >&2
        return 1
    fi
    shift
    echo "-> ip netns exec $c $*"
    ip netns exec "$c" "$@"
}

netns_peek() {
    c="${1:-}"
    if [ -z "$c" ]; then
        echo "usage: netns peek <container-name> [command...]" >&2
        return 1
    fi
    shift
    if [ "$#" -eq 0 ]; then
        set -- "${SHELL:-/bin/sh}"
    fi
    echo "-> nsenter --net=/run/netns/$c $*"
    nsenter --net=/run/netns/"$c" "$@"
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        attach)
            shift
            netns_attach "$@"
            exit 0
            ;;
        detach)
            shift
            netns_detach "$@"
            exit 0
            ;;
        list)
            shift
            netns_list "$@"
            exit 0
            ;;
        exec)
            shift
            netns_exec "$@"
            exit 0
            ;;
        peek)
            shift
            netns_peek "$@"
            exit 0
            ;;
        *)
            "$PKG_LIB_DIR/einar2-help.sh" netns
            exit 1
            ;;
    esac
    shift
done

if [ "${1-}" = '--' ]; then
    shift
fi
