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
$version = "2.2"; 

// default map drawing framework 
// (gmaps = google maps, openlayers = openlayers/osm)
//$mapapi = "gmaps";
$mapapi = "openlayers";

// openlayers additional map layers
// OpenCycleMap (0 = no, 1 = yes)
$layer_ocm = 1;
// MapQuest-OSM (0 = no, 1 = yes)
$layer_mq = 1;
// osmapa.pl (0 = no, 1 = yes)
$layer_osmapa = 1;
// UMP (0 = no, 1 = yes)
$layer_ump = 1;

// default coordinates for initial map
$init_latitude = 52.23;
$init_longitude = 21.01;

// you may set your google maps api key
// this is not obligatory by now
//$gkey = "";

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

// admin user who has access to all users locations
$admin_user = "";

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
