## Network Namespaces

Docker saves its network namespaces in `/run/docker/netns`, separated to the system's `/run/netns`.
Einar2 provides a utility to attach those namespaces to your system.

Once your containers are up and running, check their existence on the host.
You require superuser privileges to include docker private network namespace directory on the list.

```
lsns -t net
```

Knowing that, you can now let einar2 attach them to your system:

```
einar2 run util netns-attach
```

List the network namespaces again and you'll find that each of them now has as alias the container's name:

```
lsns -t net
```

This allows you to check the interfaces available inside the containers, as well as their configured routes:

```
ip netns exec <container-name> ip link show
ip netns exec <container-name> ip route show
```

You can also run a new process under that namespaces and it will have the same network restrictions as your container:

```
nsenter --net=/run/netns/<container-name> <command>
```

To detach the network namespaces of your host, run the following einar2 utility:

```
einar2 run util netns-detach
```

If you don't do this before spinning down the containers, the network namespaces will linger on your system.
Docker will not re-use them, so you'll have to delete them by hand:

```
ip netns delete <netns-basepath> <netns-pid>
```

### Resources

* https://mylinux.work/guides/docker-networking/
* https://labs.iximiuz.com/tutorials/container-networking-from-scratch
* https://4sysops.com/archives/macvlan-network-driver-assign-mac-address-to-docker-containers/
* https://iximiuz.com/en/series/debunking-container-myths/
* https://www.youtube.com/watch?v=6v_BDHIgOY8
* https://github.com/oneuptime/blog/tree/master/posts/2026-03-20-list-all-network-namespaces-linux
* https://stackoverflow.com/questions/67971506/use-unshare-to-start-process-in-existing-net-namespace
* https://4stm4.website/linux/2026/06/13/linux_native_container_en.html
* http://events17.linuxfoundation.org/sites/events/files/slides/MINCS_OSSJapan_2017_0602.pdf?trk=public_post_comment-text
* https://github.com/alpinelinux/alpine-make-rootfs
* https://oneuptime.com/blog/post/2026-03-20-route-traffic-between-namespaces/view
