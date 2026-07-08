FROM quagga-base:latest

RUN apk add --no-cache tcpdump
RUN cp -r /etc/quagga /etc/quagga.defaults

ENTRYPOINT ["/entrypoint.sh"]
