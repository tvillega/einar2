FROM alpine-base:latest

RUN apk add --no-cache quagga

RUN echo "zebra=yes" > /etc/quagga/daemons
RUN touch /etc/quagga/zebra.conf

RUN rm /entrypoint.sh # inherited
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
