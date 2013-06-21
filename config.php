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
$version = "2.0"; 

// map drawing framework 
// (gmaps = google maps, openlayers = openlayers/osm)
$mapapi = "gmaps";
//$mapapi = "openlayers";

// you may add your google maps api key
// this is not obligatory by now
//$gkey =

// MySQL config
$dbhost = ""; // mysql host, eg. localhost
$dbuser = ""; // database user
$dbpass = ""; // database pass
$dbname = ""; // database name
$salt = ""; // fill in random string here, it will increase security of password hashes

// other
// require login/password authentication 
// (0 = no, 1 = yes)
$require_authentication = 1;

// allow automatic registration of new users 
// (0 = no, 1 = yes)
$allow_registration = 0;

// Default interval in seconds for live auto reload
$interval = 10; 

// Default language
// (en, pl, de)
$lang = "en";
//$lang = "pl";
//$lang = "de";

// units
// (metric, imperial)
$units = "metric";
//$units = "imperial";

?>
