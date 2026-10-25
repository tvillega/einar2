e2() {
    local s="$1" ; shift

    case "$s" in
        ls)
            einar2 run list services "$@"
            ;;
        ln)
            einar2 run list networks "$@"
            ;;
        up)
            einar2 run start "$@"
            ;;
        down)
            einar2 run stop "$@"
            ;;
        sh)
            einar2 run shell "$@"
            ;;
        *)
            einar2 "$c" "$@"
            ;;
    esac
}
