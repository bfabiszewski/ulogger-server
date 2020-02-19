FROM alpine:3.8

LABEL maintainer="Bartek Fabiszewski (https://github.com/bfabiszewski)"

ARG DB_ROOT_PASS=secret1
ARG DB_USER_PASS=secret2
# supported drivers: mysql, pgsql, sqlite
ARG DB_DRIVER=mysql

ENV ULOGGER_ADMIN_USER admin
ENV ULOGGER_DB_DRIVER ${DB_DRIVER}
ENV ULOGGER_ENABLE_SETUP 0

ENV LANG=en_US.utf-8

RUN apk add --no-cache \
    nginx \
    php7-ctype php7-fpm php7-json php7-pdo php7-session php7-simplexml php7-xmlwriter
RUN if [ "${DB_DRIVER}" = "mysql" ]; then apk add --no-cache mariadb mariadb-client php7-pdo_mysql; fi
RUN if [ "${DB_DRIVER}" = "pgsql" ]; then apk add --no-cache postgresql postgresql-client php7-pdo_pgsql; fi
RUN if [ "${DB_DRIVER}" = "sqlite" ]; then apk add --no-cache sqlite php7-pdo_sqlite; fi

COPY .docker/run.sh /run.sh
RUN chmod +x /run.sh
COPY .docker/init.sh /init.sh
RUN chmod +x /init.sh
COPY .docker/nginx.conf /etc/nginx/conf.d/default.conf
RUN chown nginx.nginx /etc/nginx/conf.d/default.conf

RUN rm -rf /var/www/html
RUN mkdir -p /var/www/html
COPY . /var/www/html

RUN /init.sh "${DB_ROOT_PASS}" "${DB_USER_PASS}"

RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log && \
    ln -sf /dev/stdout /var/log/php7/error.log && \
    ln -sf /dev/stderr /var/log/php7/error.log

EXPOSE 80

VOLUME ["/data"]

CMD ["/run.sh"]
