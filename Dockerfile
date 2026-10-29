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
      php-cgi \
      docker-cli \
      docker-cli-compose

COPY dist/einar2-utils.tar.gz /tmp/
RUN tar -xzf /tmp/einar2-utils.tar.gz -C / && rm /tmp/einar2-utils.tar.gz

RUN mkdir -pv /var/lib/php/sessions /etc/quagga

COPY www/               /einar2/
COPY docs/              /einar2/docs/
COPY share/archetypes/  /einar2/archetypes/
COPY share/entrypoints/ /einar2/entrypoints/
COPY share/server/      /einar2/server/
COPY share/webui/       /einar2/

RUN touch /etc/quagga/zebra.conf && echo "zebra=yes" > /etc/quagga/daemons

RUN chown -R lighttpd:lighttpd /einar2 /var/lib/php/sessions
RUN chmod +x /einar2/entrypoint.sh /einar2/jailbreak.sh /einar2/entrypoints/*.sh

ENV EDITOR=nano

EXPOSE 80

ENTRYPOINT ["/einar2/entrypoint.sh"]
