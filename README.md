# Einar2

Einar Is Not A Router v2, networking laboratory on docker.

## Setup

### Environment

(Optional) Create an alias:

```
alias e2='./einar2'
```

Do notice that `sudo` won't have this alias available.

Setup a local registry on your computer:

```
e2 run up registry
```

Build the `einar2` image:

```
docker build -t localhost:5000/einar2 build/einar2
```

Save the image to the local registry:

```
docker push localhost:5000/einar2
```

Now you can edit and rebuild all the `e2-*` images without rate limiting the official docker registry.

### Network Namespaces

Docker saves its network namespaces in `/run/docker/netns`, separated to the system's `/run/netns`.
Einar2 provides a utility to attach those namespaces to your system:

Once your containers are up and running, check their existance on the host.
You require superuser privileges to include docker private network namespace directory on the list.

```
lsns -t net
```

Knowing that, you can now let einar2 attach them to your system:

```
e2 run util netns-attach
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
e2 run util detach-netns
```

If you don't do this before spinning down the containers, the network namespaces will linger on your system.
Docker will not re-use them, so you'll have to delete them by hand:

```
ip netns delete <netns-basepath> <netns-pid>
```
