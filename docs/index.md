-> Nice picture here <-

## What is Einar2?

Einar2 is a docker router simulator that is meant to aid students in learning routing protocols. It can also be used to teach docker networking and kernel network namespacing.

## Who is it made by?

It is made by a single student at the Faculty of Physic Sciences and Mathematics of Universidad de Chile.

## How does it work?

The docker image is based on Alpine which is a lightweight Linux distribution tailored for containerization that can be run on any operating system. The container is used as Docker-out-of-Docker to orchestrate multiple instances of itself, each of the containers uses its own entrypoint to simulate a different machine.

## How do I get started?

Create a directory on your system and enter it:

```
mkdir path/to/work && cd path/to/work
```

Create the following `docker-compose.yml`:

```
services:

  einar2-webui:
    image: tvillega/einar2:latest
    container_name: einar2-webui
    restart: always
    ports:
      - "80:80"
    volumes:
      - ./config:/einar2/config
      - ./labs:/einar2/labs
```

Spin up the container:

```
docker compose up -d
```

You will get a startpage at `localhost` on your web browser where you can get started.

## Why the name Einar2?

It stands for Einar is not a router v2.
