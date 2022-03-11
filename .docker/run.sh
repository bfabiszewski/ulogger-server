#!/bin/sh

if [ "${ULOGGER_ENABLE_SETUP}" = "1" ]; then
  sed -i "s/\$enabled = false;/\$enabled = true;/" /var/www/html/scripts/setup.php;
  echo "ulogger setup script enabled"
  echo "----------------------------"
fi

if [ "zz$ULOGGER_BASE_URL" != "zz" ]; then
  sed -i "s~^\$base_url = .*$~\$base_url = \"${ULOGGER_BASE_URL}\";~g" /var/www/html/config.php
fi


# show config variables
echo "ulogger configuration"
echo "---------------------"
grep '^\$' /var/www/html/config.php

# start services
if [ "$ULOGGER_DB_DRIVER" = "pgsql" ]; then
  su postgres -c 'pg_ctl -D /data/pgsql start'
elif [ "$ULOGGER_DB_DRIVER" = "mysql" ]; then
  mysqld_safe --datadir=/data/mysql &
fi
nginx
php-fpm7 -F
