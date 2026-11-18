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

labs_open() {
    if [ "$#" -eq 0 ]; then
        echo "usage: labs open <container-name>"
        exit 1
    else
        eterm docker exec -it "$1" bash
    fi
}

labs_start() {
    if [ "$#" -eq 0 ]; then
        echo "usage: labs start <lab>"
        exit 1
    else
        docker compose -f "${LABS_ROOT}/${1}/docker-compose.yml" --progress plain up
    fi
}

labs_stop() {
    if [ "$#" -eq 0 ]; then
        echo "usage: labs stop <lab>"
        exit 1
    else
        docker compose -f "${LABS_ROOT}/${1}/docker-compose.yml" --progress plain down
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
        open)
            shift
            labs_open "$@"
            exit 0
            ;;
        start)
            shift
            labs_start "$@"
            exit 0
            ;;
        stop)
            shift
            labs_stop "$@"
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
