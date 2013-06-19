<?php
/* phpTrackme
 *
 * Copyright(C) 2013 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
$version = "1.0"; 
// map drawing framework 
// (gmaps = google maps, osm = openstreetmap (not supported yet))
$mapapi = "gmaps";
// you may add your google maps api key
// this is not obligatory by now
//$gkey =

// db
$dbhost = ""; // mysql host, eg. localhost
$dbuser = ""; // database user
$dbpass = ""; // database pass
$dbname = ""; // database name
$salt = ""; // fill in random string here, it will increase security of password hashes

// other
// require login/password authentication 
// (0 = no, 1 = yes)
$require_authentication = 0;
// allow automatic registration of new users 
// (0 = no, 1 = yes)
$allow_registration = 0;
// Default interval in seconds for live auto reload
$interval = 10; 
// Default language
// (en, pl)
$lang = "en";
// units
// (metric, imperial)
$units = "metric";

?>
