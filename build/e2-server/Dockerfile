FROM alpine-base:latest

# Available in Alpine v3.24
RUN apk add --no-cache php85

EXPOSE 80

COPY index.php  /var/www/php/index.php

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
