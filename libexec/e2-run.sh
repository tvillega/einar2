
#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

run_start()     {

  local what="${1-}"

  if [[ "$what" == "registry" ]] ; then
    docker-compose -f "compose/docker-compose-registry.yml" up -d

  else
    docker-compose -f "${1%%/}/docker-compose-services.yml" up --build
  fi
}

run_stop()      {

  local what="${1-}"

  if [[ "$what" == "registry" ]] ; then
    docker-compose -f "compose/docker-compose-registry.yml" down

  else
    docker-compose -f "${1%%/}/docker-compose-services.yml" down
  fi
}

run_exec()      {
  docker exec -it $1 bash
}

run_list_services() {
  docker ps --format "table {{.Image}}\t{{.Names}}\t{{.Ports}}"
}

run_list_networks() {
  docker network ls --format "table {{.Driver}}\t{{.Name}}"
}

run_list()      {

  local what="{1-}"

  if [[ -z "$what" ]] ; then
    echo "Available listings: networks|services"
    exit
  elif [[ "$what" == "services" || "$what" == "service" ]] ; then
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

[[ -z "${1-}" ]] && exit

while [[ "$1" != "--" ]]; do case $1 in
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
  shell|sh)
    shift
    run_exec "${@}"
    exit
    ;;
  list|ls)
    shift
    run_list "${@}"
    exit
    ;;
  ln)
    shift
    run_list_networks
    exit
    ;;
  *)
    ./e2-help.sh run
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
