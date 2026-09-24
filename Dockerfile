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

RUN mkdir -pv /var/lib/php/sessions /etc/quagga

COPY config            /config.default
COPY www/              /einar2/
COPY docs              /einar2/
COPY share/archetypes  /einar2/
COPY share/server      /einar2/
COPY share/webui/      /einar2/
COPY CHANGELOG.md      /einar2/docs/releasenotes.md

RUN touch /etc/quagga/zebra.conf && echo "zebra=yes" > /etc/quagga/daemons

RUN chown -R lighttpd:lighttpd /einar2 /var/lib/php/sessions
RUN chmod +x /einar2/entrypoint.sh /einar2/jailbreak.sh

EXPOSE 80

ENTRYPOINT ["/einar2/entrypoint.sh"]
