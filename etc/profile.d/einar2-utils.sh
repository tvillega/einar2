e2() {

    if [[ $# -eq 0 ]] ; then
        e2_help
        return
    fi

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
            eterm einar2 run shell "$@"
            ;;
        do)
            einar2 ipc do "$@"
            ;;
        *)
            e2_help
            ;;
    esac
}

e2_help() {

cat << EOF
usage: e2 [SHORTHAND]

Shorthands for common einar2 commands:

  ls         -> einar2 run list services
  ln         -> einar2 run list networks
  up <lab>   -> einar2 run start <lab>
  down <lab> -> einar2 run stop <lab>
  sh         -> einar2 run shell <lab>
  do <ins>   -> einar2 ipc do <ins>

Run einar2 <subcommand> to read about a specific subcommand.
Authored by Tom Villegas (tvillega)
EOF

}
