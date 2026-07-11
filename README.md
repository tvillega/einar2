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
