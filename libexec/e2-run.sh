
#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

run_start()     {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run start <lab>"
    exit

  elif [[ "$what" == "registry" ]] ; then
    docker compose -f "compose/docker-compose-registry.yml" up -d

  else
    docker compose -f "${1%%/}/docker-compose.yml" up
  fi
}

run_stop()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run stop <lab>"
    exit

  elif [[ "$what" == "registry" ]] ; then
    docker compose -f "compose/docker-compose-registry.yml" down

  else
    docker compose -f "${1%%/}/docker-compose.yml" down
  fi
}

run_shell()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run shell <container>"
    exit

  else
    docker exec -it "$what" bash
  fi
}

run_list_services() {
  docker ps --format "table {{.Image}}\t{{.Names}}\t{{.Ports}}"
}

run_list_networks() {
  docker network ls --format "table {{.Driver}}\t{{.Name}}"
}

run_list()      {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: run list services|networks"
    exit
  elif [[ "$what" == "services" || "$what" == "service" || "$what" == "ls" ]] ; then
    run_list_services
    exit
  elif [[ "$what" == "networks" || "$what" == "network" ]] ; then
    run_list_networks
    exit
  else
    run_list_services
    exit
  fi 

}

run_utility() {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "You must specify an utility by its name"
    exit
  elif [[ ! -f "./utils/${what}.sh" ]] ; then
    echo "Utility not found."
    exit
  else
    ./utils/"${what}.sh"
  fi

}

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
  list)
    shift
    run_list "${@}"
    exit
    ;;
  list-networks|ln)
    shift
    run_list_networks
    exit
    ;;
  list-networks|ls)
    shift
    run_list_services
    exit
    ;;
  shell|sh)
    shift
    run_shell "${@}"
    exit
    ;;
  start|up)
    shift
    run_start "${@}"
    exit
    ;;
  stop|down)
    shift
    run_stop "${@}"
    exit
    ;;
  util)
    shift
    run_utility "${@}"
    exit
    ;;
  *)
    ./libexec/e2-help.sh run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
