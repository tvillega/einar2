#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

run_start() {
    if [ "$#" -eq 0 ]; then
        echo "usage: run start <lab>"
        exit 1
    else
        docker compose -f "${1%%/}/docker-compose.yml" up
    fi
}

run_stop() {
    if [ "$#" -eq 0 ]; then
        echo "usage: run stop <lab>"
        exit 1
    else
        docker compose -f "${1%%/}/docker-compose.yml" down
    fi
}

run_shell() {
    if [ "$#" -eq 0 ]; then
        echo "usage: run shell <container>"
        exit 1
    else
        docker exec -it "$1" sh
    fi
}

run_list_services() {
    docker ps --format "table {{.Image}}\t{{.Names}}\t{{.Ports}}"
}

run_list_networks() {
    docker network ls --format "table {{.Driver}}\t{{.Name}}"
}

run_list() {
    if [ "$#" -eq 0 ]; then
        echo "usage: run list services|networks"
        exit 1
    elif [ "$1" = "services" ]; then
        run_list_services
        exit 0
    elif [ "$1" = "networks" ]; then
        run_list_networks
        exit 0
    else
        run_list_services
        exit 0
    fi
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        list)
            shift
            run_list "$@"
            exit 0
            ;;
        shell)
            shift
            run_shell "$@"
            exit 0
            ;;
        start)
            shift
            run_start "$@"
            exit 0
            ;;
        stop)
            shift
            run_stop "$@"
            exit 0
            ;;
        *)
            "$PKG_LIB_DIR/e2-help.sh" run
            exit 0
            ;;
    esac
    shift
done

if [ "${1-}" = '--' ]; then
    shift
fi
