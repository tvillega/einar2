#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

init_utils() {
    echo "==> Updating einar2-utils..."
    echo "  -> Fetching tarball from upstream..."
    curl ${UPSTREAM_UTILS_URL} --output "${LOCAL}/${UTILS_TARBALL}"
    if [ "$?" -ne 0 ]; then
        echo "==> ERROR: Failed to fetch. Did you setup the server?"
        exit 1
    fi
    echo "  -> Installing utilities on system..."
    tar -xzf "${LOCAL}/${UTILS_TARBALL}" -C /
}

init_image() {
    echo "==> Updating einar2 image..."
    echo "  -> Fetching tarball from upstream..."
    curl "${UPSTREAM_IMAGE_URL}" --output "${LOCAL}/${IMAGE_TARBALL}"
    if [ "$?" -ne 0 ]; then
        echo "==> ERROR: Failed to fetch. Did you setup the server?"
        exit 1
    fi
    echo "  -> Installing image on system..."
    docker load < "${LOCAL}/${IMAGE_TARBALL}"
}

init_daemon_docker() {
    echo "  -> Enabling docker daemon..."
    systemctl enable --now docker.service
    if [ "$?" -ne 0 ]; then
        echo "==> ERROR: Failed to start daemon. Is docker installed?"
        exit 1
    fi
}

init_daemon_einard() {
    echo "  -> Enabling einar daemon..."
    systemctl enable --now einard.service
    if [ "$?" -ne 0 ]; then
        echo "==> ERROR: Failed to start daemon. Is einar2-utils installed?"
        exit 1
    fi
}

init_daemon_reload() {
    echo "  -> Reloading available daemons..."
    systemctl daemon-reload
}

init_daemon() {
    echo "==> Setting up persistent daemons..."
    init_daemon_reload
    init_daemon_docker
    init_daemon_einard
}

init_group() {
    echo "==> Setting up system groups..."
    echo "  -> Checking if autologin exists..."
    getent group autologin
    if [ "$?" -eq 0 ]; then
        echo "  -> Found. Skipping."
    else
        echo "  -> Not found. Creating..."
        groupadd -r autologin
    fi
    echo "==> Setting up user groups..."
    echo "  -> Adding "$(id -un)" to autologin..."
    gpasswd -a "$(id -un)" autologin
}

init_install() {
    init_utils
    init_image
    init_daemon
    init_group
}

init_selfupdate() {
    init_utils
    init_daemon_reload
    init_daemon_einard
    init_image
}

[ -z "${1-}" ] && exit 0

while [ "${1-}" != "--" ] && [ -n "${1-}" ]; do
    case "$1" in
        install)
            shift
            init_autoconfig "$@"
            exit 0
            ;;
        selfupdate)
            shift
            init_selfupdate "$@"
            exit 0
            ;;
        utils)
            shift
            init_utils "$@"
            exit 0
            ;;
        image)
            shift
            init_image "$@"
            exit 0
            ;;
        daemon)
            shift
            init_daemon "$@"
            exit 0
            ;;
        group)
            shift
            init_group "$@"
            exit 0
            ;;
        *)
            "$PKG_LIB_DIR/einar2-help.sh" init
            exit 0
            ;;
    esac
    shift
done

if [ "${1-}" = '--' ]; then
    shift
fi
