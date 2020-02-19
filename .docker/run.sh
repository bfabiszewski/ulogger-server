#!/bin/sh

if [ "${ULOGGER_ENABLE_SETUP}" = "1" ]; then
  sed -i "s/\$enabled = false;/\$enabled = true;/" /var/www/html/scripts/setup.php;
  echo "ulogger setup script enabled"
  echo "----------------------------"
fi

# show config variables
echo "ulogger configuration"
echo "---------------------"
grep '^\$' /var/www/html/config.php

# start services
if [ "$ULOGGER_DB_DRIVER" = "pgsql" ]; then
  su postgres -c 'pg_ctl -D /data start'
elif [ "$ULOGGER_DB_DRIVER" = "mysql" ]; then
  mysqld_safe --datadir=/data &
fi
nginx
php-fpm7 -F
