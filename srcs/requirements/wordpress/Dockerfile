FROM debian:bullseye
 
RUN apt update -y && apt install -y php php-fpm php-mysql php-redis php-pdo-mysql curl mariadb-client

COPY ./tools/wordpress.sh /tmp/
COPY ./tools/setup-products.sh /tmp/
COPY ./tools/inception-products-plugin.php /tmp/
COPY ./tools/test-connection.php /tmp/
COPY ./tools/products.php /tmp/
COPY ./conf/redis-config.php /tmp/

RUN chmod +x /tmp/wordpress.sh
RUN chmod +x /tmp/setup-products.sh

RUN mkdir -p /run/php

ENTRYPOINT ["./tmp/wordpress.sh"]

CMD ["php-fpm7.4", "-F"]