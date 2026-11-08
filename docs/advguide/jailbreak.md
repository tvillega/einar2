## Jailbreak

Einar2 is designed to be independent from its host by containing all of its resources inside a docker image.
However, due to the nature of containerization it is unable to run commands outside its namespace.

Unfortunately, because of limitations with X11 and XWayland this will only work under the root account (UID 0).

### Einar-out-of-Einar (EooE)

Install `einar2-utils` and enable its systemd unit:

```
systemctl enable --now einard
```

We will bind-mount its socket `/var/run/einar2.sock` to the Einar2 container,
this will allow it to talk to `einard` through its API.

### Docker Compose

Changing the entrypoint enables jailbreak mode in Einar2 settings.

```
services:

  einar2-webui:
    image: tvillega/einar2:latest
    container_name: einar2-webui
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /root/Desktop/config:/einar2/config
      - /root/Desktop/labs:/einar2/labs
      - /etc/einar2/webui.json:/einar2/webui.json:ro
      - /var/run/einar2.sock:/var/run/einar2.sock:ro
    entrypoint: /einar2/jailbreak.sh
```

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
