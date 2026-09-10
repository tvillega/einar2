#!/bin/bash

quagga_dir="/etc/quagga"
zebra_file="${quagga_dir}/zebra.conf"
daemons_file="${quagga_dir}/daemons"

echo ":: Starting sanity checks for quagga"

echo ":: Checking existence of zebra.conf"
sleep 1

if [[ ! -e "$zebra_file" ]] ; then
  echo "  -> Not found, creating empty file"
  touch "$zebra_file"

else
  echo "  -> File zebra.conf found, importing configurations"
fi

echo ":: Checking existence of daemons file"
sleep 1

if [[ ! -e "$daemons_file" ]] ; then
  echo "  -> Not found, creating default file"
  echo "zebra=yes" > /etc/quagga/daemons

else
  echo "  -> File daemons found, importing configurations"
fi

echo ":: Sanity checks completed, dropping to quagga"

exec /usr/sbin/zebra
