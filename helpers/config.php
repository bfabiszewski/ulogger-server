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

class uConfig {
    // version number
    static $version = "0.2-beta";

    // default map drawing framework
    // (gmaps = google maps, openlayers = openlayers/osm)
    static $mapapi = "openlayers";

    // openlayers additional map layers
    // OpenCycleMap (0 = no, 1 = yes)
    static $layer_ocm = 1;
    // MapQuest-OSM (0 = no, 1 = yes)
    static $layer_mq = 1;
    // osmapa.pl (0 = no, 1 = yes)
    static $layer_osmapa = 1;
    // UMP (0 = no, 1 = yes)
    static $layer_ump = 1;

    // default coordinates for initial map
    static $init_latitude = 52.23;
    static $init_longitude = 21.01;

    // you may set your google maps api key
    // this is not obligatory by now
    //$gkey = "";

    // MySQL config
    static $dbhost = ""; // mysql host, eg. localhost
    static $dbuser = ""; // database user
    static $dbpass = ""; // database pass
    static $dbname = ""; // database name

    // other
    // require login/password authentication
    // (0 = no, 1 = yes)
    static $require_authentication = 1;

    // all users tracks are visible to authenticated user
    // (0 = no, 1 = yes)
    static $public_tracks = 0;

    // admin user who has access to all users locations
    // none if empty
    static $admin_user = "";

    // Default interval in seconds for live auto reload
    static $interval = 10;

    // Default language
    // (en, pl, de, hu, fr, it)
    static $lang = "en";

    // units
    // (metric, imperial)
    static $units = "metric";

    private static $fileLoaded = false;
    public static $rootDir;

    public function __construct() {
        self::$rootDir = dirname(__DIR__);
        $this->setFromFile();
        $this->setFromCookies();
   }

   private function setFromFile() {
        $configFile = self::$rootDir . "/config.php";
        if (self::$fileLoaded || !file_exists($configFile)) {
            return;
        }
        self::$fileLoaded = true;
        include_once($configFile);

        if (isset($mapapi)) { self::$mapapi = $mapapi; }
        if (isset($layer_ocm)) { self::$layer_ocm = $layer_ocm; }
        if (isset($layer_mq)) { self::$layer_mq = $layer_mq; }
        if (isset($layer_osmapa)) { self::$layer_osmapa = $layer_osmapa; }
        if (isset($layer_ump)) { self::$layer_ump = $layer_ump; }
        if (isset($init_latitude)) { self::$init_latitude = $init_latitude; }
        if (isset($init_longitude)) { self::$init_longitude = $init_longitude; }
        if (isset($dbhost)) { self::$dbhost = $dbhost; }
        if (isset($dbuser)) { self::$dbuser = $dbuser; }
        if (isset($dbpass)) { self::$dbpass = $dbpass; }
        if (isset($dbname)) { self::$dbname = $dbname; }
        if (isset($require_authentication)) { self::$require_authentication = (bool) $require_authentication; }
        if (isset($public_tracks)) { self::$public_tracks = $public_tracks; }
        if (isset($admin_user)) { self::$admin_user = $admin_user; }
        if (isset($interval)) { self::$interval = $interval; }
        if (isset($lang)) { self::$lang = $lang; }
        if (isset($units)) { self::$units = $units; }
   }
   private function setFromCookies() {
        if (isset($_COOKIE["ulogger_api"])) { self::$mapapi = $_COOKIE["ulogger_api"]; }
        if (isset($_COOKIE["ulogger_lang"])) { self::$lang = $_COOKIE["ulogger_lang"]; }
        if (isset($_COOKIE["ulogger_units"])) { self::$units = $_COOKIE["ulogger_units"]; }
        if (isset($_COOKIE["ulogger_interval"])) { self::$interval = $_COOKIE["ulogger_interval"]; }
   }
}

?>