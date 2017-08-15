#!/bin/sh

DB_ROOT_PASS=$1
DB_USER_PASS=$2

mkdir -p /run/mysqld
mkdir -p /run/nginx
chown mysql:mysql /run/mysqld
chown nginx:nginx /run/nginx

mysql_install_db --user=mysql
mysqld_safe &
mysqladmin --silent --wait=30 ping
mysqladmin -u root password "${DB_ROOT_PASS}"
mysql -u root -p${DB_ROOT_PASS} < /var/www/html/scripts/ulogger.sql
mysql -u root -p${DB_ROOT_PASS} -e "CREATE USER 'ulogger'@'localhost' IDENTIFIED BY '${DB_USER_PASS}'"
mysql -u root -p${DB_ROOT_PASS} -e "GRANT ALL PRIVILEGES ON ulogger.* TO 'ulogger'@'localhost'"
mysql -u root -p${DB_ROOT_PASS} -e "CREATE USER 'ulogger'@'%' IDENTIFIED BY '${DB_USER_PASS}'"
mysql -u root -p${DB_ROOT_PASS} -e "GRANT ALL PRIVILEGES ON ulogger.* TO 'ulogger'@'%'"
mysql -u root -p${DB_ROOT_PASS} -e "INSERT INTO users (login, password) VALUES ('admin', '\$2y\$10\$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq')" ulogger
mysqladmin -u root -p${DB_ROOT_PASS} shutdown

sed -i "s/^\$dbhost = .*$/\$dbhost = \"localhost\";/" /var/www/html/config.php
sed -i "s/^\$dbname = .*$/\$dbname = \"ulogger\";/" /var/www/html/config.php
sed -i "s/^\$dbuser = .*$/\$dbuser = \"ulogger\";/" /var/www/html/config.php
sed -i "s/^\$dbpass = .*$/\$dbpass = \"${DB_USER_PASS}\";/" /var/www/html/config.php
