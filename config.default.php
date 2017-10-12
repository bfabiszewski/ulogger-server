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

// default map drawing framework
//$mapapi = "gmaps"; // google maps
//$mapapi = "openlayers"; // openlayers 2
$mapapi = "openlayers3"; // openlayers 3+

// openlayers additional map layers in XYZ format
// name => url
$ol_layers['OpenCycleMap'] = 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png'; // ?apikey=[API_KEY]
$ol_layers['OpenTopoMap'] = 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png';
$ol_layers['ESRI'] = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
$ol_layers['UMP'] = 'http://{1-3}.tiles.ump.waw.pl/ump_tiles/{z}/{x}/{y}.png';
$ol_layers['Osmapa.pl'] = 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png';


// default coordinates for initial map
$init_latitude = 52.23;
$init_longitude = 21.01;

// you may set your google maps api key
$gkey = "";

// MySQL config
$dbhost = ""; // mysql host, eg. localhost
$dbuser = ""; // database user
$dbpass = ""; // database pass
$dbname = ""; // database name
$dbprefix = ""; // optional table names prefix, eg. "ulogger_"

// other
// require login/password authentication
// (0 = no, 1 = yes)
$require_authentication = 1;

// all users tracks are visible to authenticated user
// (0 = no, 1 = yes)
$public_tracks = 0;

// admin user, who
// - can add new users
// - can edit all tracks, users
// - has access to all users locations
// none if empty
$admin_user = "";

// miniumum required length of user password
$pass_lenmin = 12;

// required strength of user password
//   0 = no requirements,
//   1 = require mixed case letters (lower and upper),
//   2 = require mixed case and numbers,
//   3 = require mixed case, numbers and non-alphanumeric characters
$pass_strength = 2;

// Default interval in seconds for live auto reload
$interval = 10;

// Default language
// (en, pl, de, hu)
$lang = "en";
//$lang = "pl";
//$lang = "de";
//$lang = "hu";
//$lang = "fr";
//$lang = "it";

// units
// (metric, imperial)
$units = "metric";
//$units = "imperial";

?>
