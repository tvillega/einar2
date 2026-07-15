FROM localhost:5000/einar2:latest

RUN cp -r /etc/quagga /etc/quagga.defaults

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
