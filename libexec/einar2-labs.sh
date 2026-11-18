#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

jsonl_to_array() {
    awk '
    BEGIN {
        print "["
    }

    # Process non-empty lines
    NF {
        if (prev != "") {
            print prev ","
        }
        prev = $0
    }

    END {
        if (prev != "") {
            print prev
        }
        print "]"
    }
    '
}

labs_list_all() {
    docker ps -a --no-trunc --format '{"Name": "{{ .Names }}", "Image": "{{ .Image }}", "Networks": "{{ .Networks }}", "State": "{{ .State }}"}' | jsonl_to_array
}

labs_list() {
    if [ "$#" -eq 0 ]; then
        echo "usage: labs list all"
        exit 1
    elif [ "$1" = "all" ]; then
        labs_list_all
        exit 0
    fi
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        list)
            shift
            labs_list "$@"
            exit 0
            ;;
        *)
            "$PKG_LIB_DIR/e2-help.sh" labs
            exit 0
            ;;
    esac
    shift
done

if [ "${1-}" = '--' ]; then
    shift
fi
