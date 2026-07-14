FROM alpine:3.24

RUN apk update
RUN apk add --no-cache \
      bash \
      nano \
      tmux \
      curl \
      iproute2 \
      php85 \
      tcpdump \
      quagga \
      bind \
      bind-tools \
      traceroute \
      lighttpd \
      php-cgi

RUN mkdir -v /archetypes /compose /labs /libexec /utils /einar2
RUN mkdir -vp /www /var/lib/php/sessions
RUN chown -R lighttpd:lighttpd /www /var/lib/php/sessions

COPY lighttpd.conf /etc/lighttpd/lighttpd.conf

SHELL ["/bin/bash", "-c"]

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
