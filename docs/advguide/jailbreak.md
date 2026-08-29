## Jailbreak

Einar2 is designed to be independent from its host by containing all of its resources inside a docker image.
However, there will be situations where you will want to interact with the host and run commands outside the container.

The Einar2 VM runs a jailbreaked container that can run arbitrary commands on the host.
To achieve this it is necessary to escape a host shell through a named pipe.

Start by creating named pipes on the host:

```
mkfifo -m a+rw ./ch0
mkfifo -m a+rw ./ch1
```

Spin up a `docker-compose.yml` that bind mounts the named pipes:

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
      - type: bind    
        source: ./ch0
        target: /einar2/ch0
      - type: bind
        source: ./ch1
        target: /einar2/ch1

    entrypoint: /einar2/jailbreak.sh
```

Run the following in a host terminal:

```
while true; do (read -r < ch0 && exec $REPLY &> ch1); done
```

Now your jailbreaked einar2 is capable of running commands on your host machine.

* **ch0**: handles `fd0` (standard input)
* **ch1**: handles `fd1` and `fd2` combined (standard output + standard error output)

### Resources

* https://stackoverflow.com/questions/75322808/linux-named-pipe-mounted-on-docker-volume-showing-as-regular-file
* https://stackoverflow.com/questions/4113986/example-of-using-named-pipes-in-linux-shell-bash
* https://stackoverflow.com/questions/14066992/what-does-minus-mean-in-exec-3-and-how-do-i-use-it
* https://stackoverflow.com/questions/3173131/redirect-copy-of-stdout-to-log-file-from-within-bash-script-itself
* https://stackoverflow.com/questions/26965342/how-to-set-a-timeout-on-fopen-with-named-pipes
* https://stackoverflow.com/questions/25249892/how-to-prevent-fopen-from-hanging-when-opening-up-a-named-pipe-in-php
* https://www.php.net/manual/en/function.stream-set-blocking.php
