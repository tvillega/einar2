#!/bin/bash

set -euo pipefail
IFS=$'\n\t'

PKG_LIBX_DIR=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
PKG_ROOT_DIR=$( cd "$PKG_LIBX_DIR/.." ; pwd -P )
PKG_ARCH_DIR=$( cd "$PKG_ROOT_DIR/config/archetype.d" ; pwd -P )

IMG="${DOCKER_IMAGE}"

gen_computer() {

  local name="${1-}"

  if [[ -z "$name" ]] ; then
    echo "usage: gen computer <name>"
    exit
  fi

  cat "$PKG_ARCH_DIR/service-computer.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ Image }}|$IMG|g" \
    | sed "s|{{ Name }}|$name|g" 

}

gen_server() {

  local name="${1-}"
  local port="${2-}"

  if [[ -z "$name" || -z "$port" ]] ; then
    echo "usage: gen server <name> <port>"
    exit
  fi

  cat "$PKG_ARCH_DIR/service-server.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ Image }}|$IMG|g" \
    | sed "s|{{ Name }}|$name|g" \
    | sed "s|{{ Port }}|$port|g"

}

gen_router() {

  local name="${1-}"

  if [[ -z "$name" ]] ; then
    echo "usage: gen router <name>"
    exit
  fi

  cat "$PKG_ARCH_DIR/service-router.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ Image }}|$IMG|g" \
    | sed "s|{{ Name }}|$name|g"

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

  cat "$PKG_ARCH_DIR/join-network.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ Address }}|$address|g" \
    | sed "s|{{ AddressDashed }}|$address_dashed|g" \
    | sed "s|{{ Network }}|$network|g" \
    | sed "s|{{ NetworkDashed }}|$network_dashed|g"

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

  cat "$PKG_ARCH_DIR/network-bridge.yml" \
    | sed 's/{#[^}]*#}//g' \
    | sed "s|{{ Network }}|$network|g" \
    | sed "s|{{ NetworkDashed }}|$network_dashed|g" \
    | sed "s|{{ Mask }}|$mask_no_leading_slash|g" \
    | sed "s|{{ Gateway }}|$gateway|g"

}

gen_compose_services() {

  cat "$PKG_ARCH_DIR/compose-services.yml" \
    | sed 's/{#[^}]*#}//g'

}

gen_compose_networks() {

  cat "$PKG_ARCH_DIR/compose-networks.yml" \
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
  echo "$(gen_compose_services)" > "$dir/docker-compose.yml"

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
    $PKG_LIBX_DIR/e2-help.sh gen
    exit
    ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi
