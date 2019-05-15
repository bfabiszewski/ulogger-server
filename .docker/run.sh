#!/bin/sh

# set config variables
sed -i "s/^\$admin_user = .*$/\$admin_user = \"${ULOGGER_ADMIN_USER}\";/" /var/www/html/config.php
sed -i "s/^\$pass_strength = .*$/\$pass_strength = ${ULOGGER_PASS_STRENGTH};/" /var/www/html/config.php
sed -i "s/^\$pass_lenmin = .*$/\$pass_lenmin = ${ULOGGER_PASS_LENMIN};/" /var/www/html/config.php
sed -i "s/^\$require_authentication = .*$/\$require_authentication = ${ULOGGER_REQUIRE_AUTHENTICATION};/" /var/www/html/config.php
sed -i "s/^\$public_tracks = .*$/\$public_tracks = ${ULOGGER_PUBLIC_TRACKS};/" /var/www/html/config.php
sed -i "s/^\$gkey = .*$/\$gkey = \"${ULOGGER_GKEY}\";/" /var/www/html/config.php
sed -i "s/^\$lang = .*$/\$lang = \"${ULOGGER_LANG}\";/" /var/www/html/config.php
sed -i "s/^\$units = .*$/\$units = \"${ULOGGER_UNITS}\";/" /var/www/html/config.php

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
