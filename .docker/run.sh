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

# show config variables
echo "ulogger configuration"
echo "---------------------"
grep '^\$' /var/www/html/config.php

# start services 
mysqld_safe &
nginx
php-fpm7 -F
