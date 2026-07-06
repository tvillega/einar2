FROM alpine:3.24

RUN apk add --no-cache bash nano curl
SHELL ["/bin/bash", "-c"]

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
