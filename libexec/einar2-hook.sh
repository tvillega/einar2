#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

hook_run() {
    local s="$1"
    shift

    if [ "$s" -lt 0 ]; then
        for a in "$PKG_HOOK_DIR"/*pre.run; do
            [ -f "$a" ] || continue
            "$a" "$@"
        done
    else
        for a in "$PKG_HOOK_DIR"/*post.run; do
            [ -f "$a" ] || continue
            "$a" "$@"
        done
    fi
}

hook_ipc() {
    local s="$1"
    shift

    if [ "$s" -lt 0 ]; then
        for a in "$PKG_HOOK_DIR"/*pre.ipc; do
            [ -f "$a" ] || continue
            "$a" "$@"
        done
    else
        for a in "$PKG_HOOK_DIR"/*post.ipc; do
            [ -f "$a" ] || continue
            "$a" "$@"
        done
    fi
}

hook_or_disable() {
    # Default hooks are mandatory for simplicity
    # removing any of them will disable all hooks

    [ -d "${PKG_HOOK_DIR:-}" ] || exit 0
    [ -f "${PKG_HOOK_DIR}/00-default-post.run" ] || exit 0
    [ -f "${PKG_HOOK_DIR}/00-default-pre.run" ] || exit 0
    [ -f "${PKG_HOOK_DIR}/00-default-post.ipc" ] || exit 0
    [ -f "${PKG_HOOK_DIR}/00-default-pre.ipc" ] || exit 0
}

hook_or_disable

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        run)
            shift
            hook_run "$@"
            exit 0
            ;;
        ipc)
            shift
            hook_ipc "$@"
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
