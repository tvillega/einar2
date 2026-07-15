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

RUN mkdir -pv /einar2/{archetypes,docs,labs,libexec,www} /server/www /var/lib/php/sessions /etc/quagga

COPY archetypes/ /einar2/archetypes/
COPY docs/       /einar2/docs/
COPY libexec/    /einar2/libexec/
COPY www/        /einar2/

COPY config/index-server.php     /server/www/index.php
COPY config/lighttpd-einar2.conf /etc/lighttpd/einar2.conf
COPY config/lighttpd-server.conf /etc/lighttpd/server.conf

RUN touch /etc/quagga/zebra.conf && echo "zebra=yes" > /etc/quagga/daemons

COPY config/entrypoint-einar2.sh   /einar2/entrypoint.sh
COPY config/entrypoint-server.sh   /server/entrypoint.sh
COPY config/entrypoint-router.sh   /router/entrypoint.sh
COPY config/entrypoint-computer.sh /computer/entrypoint.sh

RUN chown -R lighttpd:lighttpd /einar2/www /server/www /var/lib/php/sessions

EXPORT 80

ENTRYPOINT ["/einar2/entrypoint.sh"]
