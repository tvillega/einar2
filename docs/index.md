## Welcome to Einar2

-> Nice picture here <-

#### What is Einar2?

Einar2 is a docker-image, router simulator that is meant to aid students in learning routing protocols and docker networking.

#### Who is it made by?

It is made by a single student of FCFM at Universidad de Chile, solely based on the original Einar work.

#### How does it work?

The docker-image is based on Alpine Linux, which is a lightweight distribution designed for containers. The docker-image is multi-purpose, acting as Einar2, router, server or computer depending on the entrypoint selected. It uses docker's native networking system to connect machines with one another. A web interface allows to configure the containers from pre-defined archetypes of docker services and networks, keeping the focus on the laboratory.

#### How do I get started?

Start an instance of Einar2 image on its default configuration:

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

You will get a startpage at `localhost:80` where you can get started. The license is AGPL.

#### Why the name Einar2?

The original project this is based on is called Einar, which stands for Einar is not a router.
