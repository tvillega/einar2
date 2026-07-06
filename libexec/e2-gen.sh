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


  cat "archetypes/machine-computer.yml" \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
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

  cat "archetypes/machine-server.yml" \
    | sed "s|{{ .Address }}|$address|g" \
    | sed "s|{{ .AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ .Port }}|$port|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g"

}

gen_network_bridge() {

  local network="${1-}"
  local mask="${2-}"
  local gateway="${3-}"

  if [[ -z "$network" || -z "$mask" || -z "$gateway" ]] ; then
    echo "usage: gen network <network> <mask> <gateway>"
    exit
  fi

  network_dashed=$(echo $network | sed 's|\.|-|g')
  mask_no_leading_slash="${mask##/}"


  cat "archetypes/network-bridge.yml" \
    | sed "s|{{ .Network }}|$network|g" \
    | sed "s|{{ .NetworkDashed }}|$network_dashed|g" \
    | sed "s|{{ .Mask }}|$mask_no_leading_slash|g" \
    | sed "s|{{ .Gateway }}|$gateway|g"

}

gen_compose_services() {

  cat "archetypes/compose-services.yml"

}

gen_compose_networks() {

  cat "archetypes/compose-networks.yml"

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

while [[ "$1" != "--" ]]; do case $1 in
  computer)
    shift
    gen_computer "${@}"
    exit
    ;;
  server)
    shift
    gen_server "${@}"
    exit
    ;;
  bridge|network)
    shift
    gen_network_bridge "${@}"
    exit
    ;;
  compose)
    shift
    gen_compose "${@}"
    exit
    ;;
  *)
    ./e2-help.sh gen
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
