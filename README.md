# Einar2

Einar Is Not A Router v2, networking laboratory on docker.

## Classroom Environment

Create a directory on your system and enter it:

```
mkdir path/to/work && cd path/to/work
```

Create the following `docker-compose.yaml`:

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
    entrypoint: /einar2/entrypoint.sh
```

Now you can go to `localhost` or `127.0.0.1:80` on your web browser to start using Einar2.

## CLI Usage

Learn more about the CLI by running:

```
einar2
```

## Credits

### Einar1.0

Einar stands for Einar Is Not A Router and it's a custom Knoppix VM create by group of college students in Sweden circa 2006.
All the credits of this invention, as well as the authorship of the name `Einar` goes to them.

### [michelf/php-markdown](https://github.com/michelf/php-markdown)

Their awesome library allowed me to write simple markdown files for docs and changelogs,
converting them to ready-to-use websites in two PHP lines.
