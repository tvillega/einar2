## Jailbreak

Einar2 is designed to be independent from its host by containing all of its resources inside a docker image.
However, due to the nature of containerization it is unable to run commands outside its namespace.

### 3-step jailbreak process

#### Docker-out-of-Docker (DooD)

The first step is to bind-mount `/var/run/docker.socket` to the Einar2 container,
this will allow it to manage `dockerd` through its API.

#### Einar Daemon

The second step is to run an `einard` instance on the host system.

```
cp /opt/einar2/lib/systemd/user/einard.service $HOME/.config/systemd/user/einard.service
```

Now run it with the `--user` flag:

```
systemd --user start einard
```

This daemon is in charge of running new processes on your system, to simplify launching GUI programs
you must run the daemon under your user to avoid conflicts with your display server.

#### Einar-out-of-Einar

The third step is to bind-mount `/var/run/einar.socket` to the Einar2 container,
this will allow it to manage `einard` through its API.

### Docker Compose

Changing the entrypoint enables jailbreak mode in Einar2 settings.

```
services:

  einar2-jailbreak:   
    image: tvillega/einar2:latest
    container_name: einar2-jailbreak
    restart: always 
    ports:
      - "80:80"
    volumes:
      - ./labs:/einar2/labs
      - /var/run/docker.socket:/var/run/docker.socket:ro
      - /var/run/einar.socket:/var/run/einar.socket:ro
    entrypoint: /einar2/jailbreak.sh
```

### einard API

Open directives: 

* `open::lxterminal`
* `open::wireshark`
* `open::featherpad`
* `open::firefox::docs`
* `open::firefox::labs`
* `open::firefox::einar1`

System directives:

* `system::os-release`
* `system::network::dhcpcd`
* `system::keyboard::swedish`
* `system::keyboard::english`
* `system::mouse::focus-click`
* `system::mouse::focus-hover`
* `system::session::shutdown`
* `system::session::reboot`
* `system::session::logout`

### Resources

* https://stackoverflow.com/questions/75322808/linux-named-pipe-mounted-on-docker-volume-showing-as-regular-file
* https://stackoverflow.com/questions/4113986/example-of-using-named-pipes-in-linux-shell-bash
* https://stackoverflow.com/questions/14066992/what-does-minus-mean-in-exec-3-and-how-do-i-use-it
* https://stackoverflow.com/questions/3173131/redirect-copy-of-stdout-to-log-file-from-within-bash-script-itself
* https://stackoverflow.com/questions/26965342/how-to-set-a-timeout-on-fopen-with-named-pipes
* https://stackoverflow.com/questions/25249892/how-to-prevent-fopen-from-hanging-when-opening-up-a-named-pipe-in-php
* https://www.php.net/manual/en/function.stream-set-blocking.php
* https://unix.stackexchange.com/questions/698278/custom-location-for-systemd-services
* https://superuser.com/questions/45342/when-should-i-use-dev-shm-and-when-should-i-use-tmp
* https://linuxvox.com/blog/devshm-in-linux/
* https://www.geeksforgeeks.org/operating-systems/inter-process-communication-ipc/
* https://lours.me/posts/compose-tip-069-pid-ipc/
