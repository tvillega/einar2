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

COPY config  /config.default
COPY docs/   /einar2/assets/
COPY www/    /einar2/
RUN rm -rf   /einar2/archetypes # from devel env

RUN touch /etc/quagga/zebra.conf && echo "zebra=yes" > /etc/quagga/daemons

RUN chown -R lighttpd:lighttpd /einar2 /var/lib/php/sessions
RUN chmod +x /config.default/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/config.default/entrypoint.sh"]
