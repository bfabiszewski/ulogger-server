<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// This is default configuration file.
// Copy it to config.php and customize

// Database config

// PDO data source name, eg.:
// mysql:host=localhost;port=3307;dbname=ulogger;charset=utf8
// mysql:unix_socket=/path/to/mysql.sock;dbname=ulogger;charset=utf8
// pgsql:host=localhost;port=5432;dbname=ulogger
// sqlite:/path/to/ulogger.db
$dbdsn = "";

// Database user name
$dbuser = "";

// Database user password
$dbpass = "";

// Optional table names prefix, eg. "ulogger_"
$dbprefix = "";

?>
