FROM localhost:5000/einar2:latest

EXPOSE 80

COPY index.php  /var/www/php/index.php

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
