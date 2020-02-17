#!/bin/sh

DB_ROOT_PASS=$1
DB_USER_PASS=$2

mkdir -p /run/nginx
chown nginx:nginx /run/nginx

# Fix permission issues on mounted volume in macOS
sed -i "s/^nobody:.*$/nobody:x:1000:50::nobody:\/:\/sbin\/nologin/" /etc/passwd
sed -i "s/^nobody:.*$/nobody:x:50:/" /etc/group

# Prepare ulogger filesystem
grep '^[$<?]' /var/www/html/config.default.php > /var/www/html/config.php
chown nobody:nobody /var/www/html/uploads
chmod 775 /var/www/html/uploads

if [ "$ULOGGER_DB_DRIVER" = "sqlite" ]; then
  sed -i "s/^\$dbuser = .*$//" /var/www/html/config.php
  sed -i "s/^\$dbpass = .*$//" /var/www/html/config.php
else
  sed -i "s/^\$dbuser = .*$/\$dbuser = \"ulogger\";/" /var/www/html/config.php
  sed -i "s/^\$dbpass = .*$/\$dbpass = \"${DB_USER_PASS}\";/" /var/www/html/config.php
fi

if [ "$ULOGGER_DB_DRIVER" = "pgsql" ]; then
  export PGDATA=/data
  mkdir -p ${PGDATA} /run/postgresql /etc/postgres
  chown postgres:postgres ${PGDATA} /run/postgresql /etc/postgres
  su postgres -c "initdb --auth-host=md5 --auth-local=trust --locale=en_US.utf-8 --encoding=utf8"
  sed -ri "s/^#(listen_addresses\s*=\s*)\S+/\1'*'/" ${PGDATA}/postgresql.conf
  echo "host all all 0.0.0.0/0 md5" >> ${PGDATA}/pg_hba.conf
  su postgres -c "pg_ctl -w start"
  su postgres -c "psql -c \"ALTER USER postgres WITH PASSWORD '${DB_ROOT_PASS}'\""
  su postgres -c "psql -c \"CREATE USER ulogger WITH PASSWORD '${DB_USER_PASS}'\""
  su postgres -c "createdb -E UTF8 -l en_US.utf-8 -O ulogger ulogger"
  su postgres -c "psql -U ulogger < /var/www/html/scripts/ulogger.pgsql"
  su postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE ulogger TO ulogger\""
  su postgres -c "psql -d ulogger -c \"GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO ulogger\""
  su postgres -c "psql -d ulogger -c \"GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO ulogger\""
  su postgres -c "psql -d ulogger -c \"INSERT INTO users (login, password, admin) VALUES ('${ULOGGER_ADMIN_USER}', '\\\$2y\\\$10\\\$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq', TRUE)\""
  su postgres -c "pg_ctl -w stop"
  sed -i "s/^\$dbdsn = .*$/\$dbdsn = \"pgsql:host=localhost;port=5432;dbname=ulogger\";/" /var/www/html/config.php
elif [ "$ULOGGER_DB_DRIVER" = "sqlite" ]; then
  mkdir -p /data/sqlite
  chown -R nobody:nobody /data
  sqlite3 -init /var/www/html/scripts/ulogger.sqlite /data/sqlite/ulogger.db .exit
  sqlite3 -line /data/ulogger.db "INSERT INTO users (login, password, admin) VALUES ('${ULOGGER_ADMIN_USER}', '\$2y\$10\$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq', 1)"
  sed -i "s/^\$dbdsn = .*$/\$dbdsn = \"sqlite:\/data\/sqlite\/ulogger.db\";/" /var/www/html/config.php
else
  mkdir -p /run/mysqld
  chown mysql:mysql /run/mysqld
  mysql_install_db --user=mysql --datadir=/data
  mysqld_safe --datadir=/data &
  mysqladmin --silent --wait=30 ping
  mysqladmin -u root password "${DB_ROOT_PASS}"
  mysql -u root -p"${DB_ROOT_PASS}" < /var/www/html/scripts/ulogger.mysql
  mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE USER 'ulogger'@'localhost' IDENTIFIED BY '${DB_USER_PASS}'"
  mysql -u root -p"${DB_ROOT_PASS}" -e "GRANT ALL PRIVILEGES ON ulogger.* TO 'ulogger'@'localhost'"
  mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE USER 'ulogger'@'%' IDENTIFIED BY '${DB_USER_PASS}'"
  mysql -u root -p"${DB_ROOT_PASS}" -e "GRANT ALL PRIVILEGES ON ulogger.* TO 'ulogger'@'%'"
  mysql -u root -p"${DB_ROOT_PASS}" -e "INSERT INTO users (login, password, admin) VALUES ('${ULOGGER_ADMIN_USER}', '\$2y\$10\$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq', TRUE)" ulogger
  mysqladmin -u root -p"${DB_ROOT_PASS}" shutdown
  sed -i "s/^\$dbdsn = .*$/\$dbdsn = \"mysql:host=localhost;port=3306;dbname=ulogger;charset=utf8\";/" /var/www/html/config.php
fi
