#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

gen_computer() {

  local address="${1-}"
  local network="${2-}"

  if [[ -z "$address" || -z "$network" ]] ; then
    echo "usage: gen computer <address> <network>"
    exit
  fi

  address_dashed=$(echo $address | sed 's|\.|-|g')
  network_dashed=$(echo $network | sed 's|\.|-|g')

  cat "archetypes/service-computer.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g"

}

gen_server() {

  local address="${1-}"
  local network="${2-}"
  local port="${3-}"

  if [[ -z "$address" || -z "$network" || -z "$port" ]] ; then
    echo "usage: gen server <address> <network> <port>"
    exit
  fi

  address_dashed=$(echo $address | sed 's|\.|-|g')
  network_dashed=$(echo $network | sed 's|\.|-|g')

  cat "archetypes/service-server.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ .Port }}|$port|g" \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g"

}

gen_router() {

  local address="${1-}"
  local network="${2-}"

  if [[ -z "$address" || -z "$network" ]] ; then
    echo "usage: gen router <address> <network>"
    exit
  fi

  address_dashed=$(echo $address | sed 's|\.|-|g')
  network_dashed=$(echo $network | sed 's|\.|-|g')

  cat "archetypes/service-router.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g"

}

join_network() {

  local address="${1-}"
  local network="${2-}"

  if [[ -z "$address" || -z "$network" ]] ; then
    echo "usage: gen join-network <address> <network>"
    exit
  fi

  address_dashed=$(echo $address | sed 's|\.|-|g')
  network_dashed=$(echo $network | sed 's|\.|-|g')

  cat "archetypes/join-network.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g"

}

gen_network_bridge() {

  local network="${1-}"
  local mask="${2-}"
  local gateway="${3-}"

  if [[ -z "$network" || -z "$mask" || -z "$gateway" ]] ; then
    echo "usage: gen bridge <network> <mask> <gateway>"
    exit
  fi

  network_dashed=$(echo $network | sed 's|\.|-|g')
  mask_no_leading_slash="${mask##/}"

  cat "archetypes/network-bridge.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g" \
    | sed "s|{{ .Mask }}|$mask_no_leading_slash|g" \
    | sed "s|{{ .Gateway }}|$gateway|g"

}

gen_network_ipvlan() {

  local network="${1-}"
  local mask="${2-}"   
  local gateway="${3-}"
  local subinterface="${4-}"

  if [[ -z "$network" || -z "$mask" || -z "$gateway" || -z "$subinterface" ]] ; then
    echo "usage: gen ipvlan <network> <mask> <gateway> <subinterface>"
    exit
  fi

  network_dashed=$(echo $network | sed 's|\.|-|g')
  mask_no_leading_slash="${mask##/}"

  cat "archetypes/network-ipvlan.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g" \
    | sed "s|{{ .Mask }}|$mask_no_leading_slash|g" \
    | sed "s|{{ .Gateway }}|$gateway|g" \
    | sed "s|{{ .Subinterface }}|$subinterface|g"
 
}

gen_compose_services() {

  cat "archetypes/compose-services.yml" \
    | sed 's/{#[^}]*#}//g'


}

gen_compose_networks() {

  cat "archetypes/compose-networks.yml" \
    | sed 's/{#[^}]*#}//g'

}

gen_compose() {

  local what="${1-}"

  if [[ -z "$what" ]] ; then
    echo "usage: gen compose <section>"
    exit
  elif [[ "$what" == "services" || "$what" == "service" ]] ; then
    shift
    gen_compose_services "${@}"
  elif [[ "$what" == "networks" || "$what" == "network" ]] ; then
    shift
    gen_compose_networks "${@}"
  else
    echo "Available compose archetypes: services|networks"
    exit
  fi

}

gen_laboratory() {

  local lab="${1-}"

  if [[ -z "$lab" ]] ; then
    echo "Lab name can't be empty"
    exit
  fi

  local dir="labs/$lab"

  mkdir -p "$dir"
  echo "$(gen_compose_networks)" > "$dir/docker-compose-networks.yml"
  echo "$(gen_compose_services)" > "$dir/docker-compose-services.yml"

}

while [[ "$1" != "--" ]]; do case $1 in
  computer|c)
    shift
    gen_computer "${@}"
    exit
    ;;
  server|s)
    shift
    gen_server "${@}"
    exit
    ;;
  router|r)
    shift
    gen_router "${@}"
    exit
    ;;
  join-network|jn)
    shift
    join_network "${@}"
    exit
    ;;
  bridge|b|network|n)
    shift
    gen_network_bridge "${@}"
    exit
    ;;
  ipvlan|v)
    shift
    gen_network_ipvlan "${@}"
    exit
    ;;
  compose)
    shift
    gen_compose "${@}"
    exit
    ;;
  laboratory|lab|l)
    shift
    gen_laboratory "${@}"
    exit
    ;;
  *)
    ./libexec/e2-help.sh gen
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
