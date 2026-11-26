#!/bin/sh

set -eu
IFS='
	'

if [ -z "${PKG_ROOT_DIR:-}" ]; then
    echo "err: ${0##*/} cannot be invoked directly" >&2
    exit 1
fi

parse_hook_field() {
    file="$1"
    section="$2"
    key="$3"

    awk -v target_sec="$section" -v target_key="$key" '
        /^[[:space:]]*(#|;|$)/ { next }
        /^\[.*\]/ {
            sec = $0
            sub(/^\[/, "", sec)
            sub(/\][[:space:]]*$/, "", sec)
            in_sec = (sec == target_sec)
            next
        }
        in_sec {
            eq = index($0, "=")
            if (eq == 0) next
            k = substr($0, 1, eq - 1)
            v = substr($0, eq + 1)
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", k)
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", v)
            if (k == target_key) {
                print v
                exit
            }
        }
    ' "$file"
}

run_matching_hooks() {
    cmd="$1" ; shift
    phase_num="$1" ; shift
    subcmd="${1:-*}"
    [ "$#" -gt 0 ] && shift

    if [ "$phase_num" -lt 0 ]; then
        phase="PreTransaction"
    else
        phase="PostTransaction"
    fi

    [ -d "${PKG_HOOK_DIR:-}" ] || return 0

    for hook_file in "$PKG_HOOK_DIR"/*.hook; do
        [ -f "$hook_file" ] || continue

        h_cmd=$(parse_hook_field "$hook_file" "Trigger" "Command")
        h_sub=$(parse_hook_field "$hook_file" "Trigger" "Subcommand")

        [ "$h_cmd" = "$cmd" ] || continue
        if [ -n "$h_sub" ] && [ "$h_sub" != "*" ] && [ "$h_sub" != "$subcmd" ]; then
            continue
        fi

        h_desc=$(parse_hook_field "$hook_file" "Action" "Description")
        h_when=$(parse_hook_field "$hook_file" "Action" "When")
        h_exec=$(parse_hook_field "$hook_file" "Action" "Exec")

        [ "$h_when" = "$phase" ] || continue
        [ -n "$h_exec" ] || continue

        if [ -n "$h_desc" ]; then
            echo ":: $h_desc"
        fi

        sh -c "$h_exec" _ "$@"
    done
}

[ "$#" -lt 2 ] && exit 0
run_matching_hooks "$@"
