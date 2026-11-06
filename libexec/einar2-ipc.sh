#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

SOCKET="/var/run/einar2.sock"

ipc_listener() {
    daemon="${PKG_BIN_DIR}/einard"

    if [ -S "$SOCKET" ]; then
        echo "Closing previous session"
        rm -f "$SOCKET"
    fi

    echo "Listening on socket $SOCKET"
    socat UNIX-LISTEN:"$SOCKET",fork,reuseaddr SYSTEM:"$daemon"
}

ipc_command() {
    cmd="${1-}"

    if [ ! -S "$SOCKET" ]; then
        echo "failed to connect to the einar API at unix://${SOCKET}"
        exit 0
    fi

    printf '%s' "$cmd" | socat - UNIX-CONNECT:"$SOCKET"
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        listener)
            shift
            ipc_listener "$@"
            exit 0
            ;;
        do)
            shift
            ipc_command "$@"
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
