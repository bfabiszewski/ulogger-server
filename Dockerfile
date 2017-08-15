FROM alpine:3.6

LABEL maintainer="Bartek Fabiszewski (https://github.com/bfabiszewski)"

ARG DB_ROOT_PASS=secret1
ARG DB_USER_PASS=secret2

ENV ULOGGER_ADMIN_USER admin
ENV ULOGGER_PASS_STRENGTH 0
ENV ULOGGER_PASS_LENMIN 5
ENV ULOGGER_REQUIRE_AUTHENTICATION 1
ENV ULOGGER_PUBLIC_TRACKS 0
ENV ULOGGER_GKEY ""
ENV ULOGGER_LANG en
ENV ULOGGER_UNITS metric

RUN apk add --no-cache mariadb mariadb-client nginx php7-ctype php7-fpm php7-json php7-mysqli php7-session php7-simplexml php7-xmlwriter

COPY .docker/run.sh /run.sh
RUN chmod +x /run.sh
COPY .docker/init.sh /init.sh
RUN chmod +x /init.sh
COPY .docker/nginx.conf /etc/nginx/conf.d/default.conf
RUN chown nginx.nginx /etc/nginx/conf.d/default.conf

RUN rm -rf /var/www/html
RUN mkdir -p /var/www/html
COPY . /var/www/html
RUN grep '^[$<?]' /var/www/html/config.default.php > /var/www/html/config.php

RUN /init.sh "${DB_ROOT_PASS}" "${DB_USER_PASS}"

RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log && \
    ln -sf /dev/stdout /var/log/php7/error.log && \
    ln -sf /dev/stderr /var/log/php7/error.log

EXPOSE 80

VOLUME ["/var/lib/mysql"]

CMD ["/run.sh"]
