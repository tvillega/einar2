FROM alpine:latest

RUN apk add --no-cache bash
SHELL ["/bin/bash", "-c"]

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
