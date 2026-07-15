# Einar2

Einar Is Not A Router v2, networking laboratory on docker.

## Classroom Environment

Create a directory on your system and enter it:

```
mkdir path/to/work && cd path/to/work
```

Use the image [tvillega/einar2:latest](https://hub.docker.com/r/tvillega/einar2) to create the following `docker-compose.yaml`:

```
services:

  einar2-website:
    image: tvillega/einar2:latest
    container_name: einar2-website
    restart: always
    ports:
      - "80:80"
    volumes:
      - ./labs:/einar2/labs
    entrypoint: /usr/sbin/lighttpd -D -f /etc/lighttpd/einar2.conf
```

Now you can go to `localhost` or `127.0.0.1:80` on your web browser to start using Einar2.

## Development Environment

The following instructions will guide you on how to deploy the project locally,
build custom einar2 images and push the limits on how much a laboratory can do.

Start a local registry on your computer:

```
einar2 run up registry
```

Build the `einar2` image and tag it as local at the same time:

```
docker build -t localhost:5000/einar2 .
```

Save the image to the local registry:

```
docker push localhost:5000/einar2
```

Now you can make folders under `build/` to create new kinds of machines, the default machines are provided as examples..
Remember to always use `localhost:5000/einar2` for the base image, that will query your local registry.

Learn more about the CLI by running:

```
einar2
```

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

## Credits

Einar stands for Einar Is Not A Router and it's a custom Knoppix VM create by group of college students in Sweden circa 2003.
All the credits of this invention, as well as the authorship of the name `Einar` goes to them.
