# Einar2

Einar2 is a program to build and deploy network laboratories under docker.

See #Credits for acknowledgements to the authors of the original Einar.

## Archetypes

Strictly speaking, you can build your own network laboratory without Einar2
by declaring containers and networks inside a `docker-compose.yml` file and
then start them with the `docker compose up` command. However, as laboratories
grow in complexity it becomes harder to keep track of relationships among the
containers. Small erros propagate and they become harder to fix.

Einar2 solves this by providing *archetypes* for containers and networks.
An archetype is a template file with variables like `{{ .Name }}` that are
replaced by values on build time. This way users only have to worry about
the values and the build process outputs a well-formated configuration block.

There are 5 predefined archetypes:

```
einar2
├── archetypes
│   ├── compose-networks.yml
│   ├── compose-services.yml
│   ├── machine-computer.yml
│   ├── machine-server.yml
│   └── network-bridge.yml
```

Run the following command to better understand how to use them:

```
$ ./einar2 gen
```

### Compose Services

This archetype is the main compose file that will be called by docker.
By default, it includes another set of compose files and starts the
*services* sections where our containers will be saved:

```
include:
  - ../../compose/docker-compose-base.yml
  - ../../compose/docker-compose-machines.yml
  - docker-compose-networks.yml

services:

```

There are no `{{ .Name }}` variables here since this is the
foundation of every laboratory. The convention is to call this
file `docker-compose-services.yml`, you can notice there
it has an include for the networks file in the same directory.

### Compose Networks

This archetype is the secondary compose file of a laboratory
and its dedicate to store all the available networks.
If it exists here, you can assume their existence and
use them inside `*-services.yml`.

```
networks:
```

> [!IMPORTANT]
> The compose file `*-services.yml` will automatically generate
its own network despite not being declared explicitly.
> Ignore this network on your laboratory's topology as
supressing it would require extra configurations *per container*.

### Machine Computer

This archetype is a service block for `*-services.yml` compose file.
It's pre-loaded with usefull utilities to debug the connections,
reach servers and ping other computers in the topology.

```
  e2-computer-{{ .AddressDashed }}:
    image: e2-computer:latest
    container_name: e2-computer-{{ .AddressDashed }}
    stdin_open: true
    tty: true
    hostname: computer-{{ .AddressDashed }}
    networks:
      net-{{ .NetworkDashed }}:
        ipv4_address: {{ .Address }}

```

The following variables are available:

* `.Address`: The IPv4 address of the commputer in the network.
* `.AddressDashed`: The IPv4 address with dashes instead of dots.
* `.Network`: The network or switch the computer is connected to.
* `.NetworkDashed`: The network or switch with dashes instead of dots.

> [!NOTE]
> `.Address` is not here used, but if present the substitution is done.

### Machine Server

This archetype is a service block for `*-services.yml` compose file.
It's pre-loaded with a php debug server with a website that shows
connection details like Server IP, Client IP and HTTP headers.

```
  e2-server-{{ .AddressDashed }}:
    image: e2-server:latest
    container_name: e2-server-{{ .AddressDashed }}
    stdin_open: true
    tty: true
    hostname: server-{{ .AddressDashed }}
    ports:
      - "{{ .Port }}:80"
    networks:
      net-{{ .NetworkDashed }}:
        ipv4_address: {{ .Address }}

```

The following variables are available:

* `.Address`: The IPv4 address of the server in the network.
* `.AddressDashed`: The IPv4 address with dashes instead of dots.
* `.Network`: The network or switch the server is connected to.        
* `.NetworkDashed`: The network or switch with dashes instead of dots.
* `.Port`: The host port the webserver will be available in your localhost.

> [!NOTE]
> `.Address` & `.Network` are not here used, but if present the substitution is done.   

### Network Bridge

This archetype is a network block for `*-networks.yml` compose file.
It provides a simple network or switch you can connect your containers to.

```
  net-{{ .NetworkDashed }}:
    name: net-{{ .NetworkDashed }}
    driver: bridge # reserves IP x.x.x.1 as default gateway, we can't re-assign it
    # internal: true # Disables docker's port bind with host
    ipam:
      driver: default
      config:
        - subnet: {{ .Network }}/{{ .Mask }}
          gateway: {{ .Gateway }} # to use a container as gateway the container's IP can't be x.x.x.1

```

* `.Network`: The IPv4 address that identifies the whole network.
* `.NetworkDashed`: The IPv4 network address but with dashes instead of dots.
* `.Mask`: The network mask.
* `.Gateway`: The IPv4 address that acts as default gateway.

> [!IMPORTANT]
> Docker always reserves `x.x.x.1` and doesn't allow to re-assign it.


---

The utility command `./einar2 gen` allows to easily build this blocks.
You can modify these archetypes and they'll continue to work with the
utility as long as the variable names are respected.


## Build (Images)

You can create a laboratory with any image you like and it will still work.
Einar2 builds its own images from `Alpine Linux v2.34` to maximize the control
over the environment and allow further customization by the user.

There are 4 predefined images:

```
├── build
│   ├── alpine-base
│   │   ├── Dockerfile
│   │   └── entrypoint.sh
│   ├── e2-computer
│   │   └── Dockerfile
│   ├── e2-server
│   │   ├── Dockerfile
│   │   ├── entrypoint.sh
│   │   └── index.php
│   └── quagga-base
│       ├── Dockerfile
│       └── entrypoint.sh
```

### Base Images

Images that are not meant to be used directly but to be
used as base for other images of the laboratory.

#### Alpine

It locks to `v2.34` since `php` is provided under versions like `phpXY` and
it has to be pinned to reference it directly on the CLI.

Additional utilities like `bash` and `nano` are included to make
the CLI experience more friendly. All Einar2 images are expected to
use this `alpine-base` image as base.

#### Quagga

It follows the same principles as `alpine-base`, except it runs a `zebra`
dameon. It was created to be used as base for any router image that requires
a zebra daemon to be run in the background.

### e2 Images

Images that are meant to be used as leaf nodes in the topology
of the laboratory. They are all prefixed with `e2-` to clearly
identify them during reverse DNS lookups e.g. under `tcpdump`.

#### Computer

Clients of servers. Test subjects to try traverse the topology
and test that all the connections work as expected in the laboratory.

They come with `tcpdump` to check how pings work in the network
and with `curl` to render webserver's information on screen.

#### Server

Servers of clients. Test subjects for load balancing, network
priorization, DMZ, among others.

They come with a debug php server that is exposed to the host.
It shows the Client IP, the Server IP and HTTP headers.


#### Others

You could build your custom `e2-something` images for more specialized
laboratories. Just follow the computer|server archetypes|dockerfiles files.

## Compose (Files)

Normally, a docker compose file consists of a single file with every
definition of service and network inside it.

Einar2 uses multiple compose files to achieve that, having a couple
shared among different laboratories. The creation of the custom images
is chained to every created laboratory so you don't have to worry
about having to setup a computer, server or router image each time.

There are 2 core compose files:

```
├── compose
│   ├── docker-compose-base.yml
│   └── docker-compose-machines.yml
```

### Base

It is in charge of building `alpine-base` and `quagga-base` images.

### Machines

It is in charge of building `e2-computer` and `e2-server` images.

### Routers (TODO)

It is in charge of building the following images:

> [!IMPORTANT]
> Einar2 tries to provide an exhaustive list of examples and presets, however
you are encouraged to adjust the program to your own needs. Particullarly,
the router images will greatly vary according to your laboratory goals.

* `e2-router-simple`: only runs zebra and provides a `vtysh` shell.
* `e2-router-rip`: simple router + rip.
* `e2-router-ospf`: simpe router + ospf.
* `e2-router-isis`: simple router + isis.
* `e2-router-nhrpd`: simple router + nhrpd.
* `e2-router-bgp`: simple router + bgp.
* `e2-router`: enabled modes defined by archetype.

> [!NOTE]
> The default `compose-services.yml` archetype does not include routers
by default. Please create a `docker-compose-routers.yml` in your laboratory
directory and add them there.

## Laboratories

This is the foundational concept of Einar2 and its main advantage over using
only docker compose. The following structure is enforced by the project:

```
├── labs
│   ├── 01-my-first-lab
│   │   ├── docker-compose-networks.yml
│   │   └── docker-compose-services.yml
│   └── 02-learning-ospf
│       ├── docker-compose-networks.yml
│       ├── docker-compose-networks.yml
│       └── docker-compose-routers.yml
```

With the exception of `*-routers.yml`, all those files are available
as archetypes and can be used to create new laboratories.

The same happens with the networks and services, they are also
available as archetypes and can be used to populate the files.

To better understand Einar2's workflow we will provide an example:

### Creating a laboratory

We start by unziping our `einar2.zip` file. You'll find the following:

```
.
├── archetypes
│   ├── compose-networks.yml
│   ├── compose-services.yml
│   ├── machine-computer.yml
│   ├── machine-server.yml
│   └── network-bridge.yml
├── build
│   ├── alpine-base
│   │   ├── Dockerfile
│   │   └── entrypoint.sh
│   ├── e2-computer
│   │   └── Dockerfile
│   ├── e2-server
│   │   ├── Dockerfile
│   │   ├── entrypoint.sh
│   │   └── index.php
│   └── quagga-base
│       ├── Dockerfile
│       └── entrypoint.sh
├── compose
│   ├── docker-compose-base.yml
│   └── docker-compose-machines.yml
├── einar2
├── labs
│   ├── 00-1-computers-in-lan
│   ├── 00-alpha.1
│   │   ├── docker-compose-networks.yml
│   │   └── docker-compose-services.yml
│   └── 00-alpha.2
│       ├── docker-compose-networks.yml
│       └── docker-compose-services.yml
├── libexec
│   ├── e2-edit.sh
│   ├── e2-gen.sh
│   ├── e2-help.sh
│   └── e2-run.sh
└── README.md
```

> [!TIP]
> You can familiarize yourself with the CLI by running the following:
> `$ ./einar2`

We start by creating a new directory for our laboratory:

```
$ mkdir labs/99-example
```

This directory will hold all of our project's files.
Now we need a compose file, for that we use an archetype:

```
$ ./einar2 gen compose services > labs/99-example/docker-compose-services.yml
```

You'll notice that this archetype makes an include of `docker-compose-networks.yml`,
we need to create that one as well.

```
$ ./einar2 gen compose networks > labs/99-example/docker-compose-networks.yml
```

Although the resulting file is almost empty, you are free to modify
the network compose archetype to have a common network among all laboratories.

Now picture the following LAN network inside a house:

```
                   ------------
                   | Internet |
                   ------------
                        ||
                     --------
                     |Router|
                     --------
                        ||
             ___________||______
             |        |        |
             |        |        |
          -----     -----    -----
          |PC1|     |PC2|    |SRV|
          -----     -----    -----
```

There are 2 computers and 1 server. Let's assume it's an average house network
with network address `192.168.0.0/24`, and that the router is managed by the ISP
and leads to the internet (our real computer in this context).

Let's start by creating the computers in our `*-services.yml`.
We have an archetype to declare it in one go:

> Our first computer will have the IP `192.168.0.11` and will be part of the network `192.168.0.0`.

```
$ ./einar2 gen computer 192.168.0.11 192.168.0.0 >> labs/99-example/docker-compose-services.yml
```

> Our second computer will have the IP `192.168.0.12` and will be part of the network `192.168.0.0`.

```
$ ./einar2 gen computer 192.168.0.12 192.168.0.0 >> labs/99-example/docker-compose-services.yml
```

For the server it will be a little different since we also have to declare the exposed port of our (real) computer to bind its service.

```
$ ./einar2 gen server 192.168.0.51 192.168.0.0 8081 >> labs/99-example/docker-compose-services.yml
```

Now let's declare the network in our `*-networks.yml`.
We also have an archetype to make the declaration straightforward:

> We are using docker's built-in gateway for the network to act as router.

> [!TIP]
> The leading `/` of the mask is ignored, but you can include it for clarity.

```
$ ./einar2 gen network 192.168.0.0 /24 192.168.0.1 >> labs/99-example/docker-compose.networks.yml
```

And that's it! We created our first laboratory under docker with the help of Einar2!

If a block has a bad setting, you can always re-generate it and replace the old block with it.

### Running a laboratory

Assuming that you followed all the previous steps, you can now run the laboratory with a single command:

> [!TIP]
> The trailing `/` is ignored, so you can run <tab> on the directory for a quick autocomplete.

```
# ./einar2 run start labs/99-example/
```

Einar2 will make all the preparations and you'll have a live laboratory.
From here onwards you can use standard `docker` commands to manipulate the containers:

* **List all active containers**

> Notice that they all have really self-explainatory names that you can easily associate to your topology diagrams.

```
# docker ps
```

* **Open a shell in one of the containers**

> Notice that they all have ready-to-use utilities like `bash`, `curl` and `tcpdump`.

```
# docker exec -it <container-name> bash
```

Since we are using docker's default gateway, we can reach the server from one of our PC's.

```
[pc1-shell]$ curl 192.168.0.51
```

This will print on screen details about the interaction to the webserver.

```
[pc1-shell]$ ping 192.168.0.2
```

```
[pc2-shell]$ tcpdump
```

By pinging a pc and checking tcpdump with the other you'll see under what names are they called inside the network.

Use Einar2 to stop the laboratory:

```
# ./einar2 run stop labs/99-example
```
