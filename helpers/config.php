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


 /**
  * Initialize on file include
  */
  uConfig::init();

 /**
  * Handles config values
  */
  class uConfig {
    // version number
    static $version = "0.4-beta";

    // default map drawing framework
    static $mapapi = "openlayers";

    // gmaps key
    static $gkey = null;

    // openlayers additional map layers
    static $ol_layers = [];

    // default coordinates for initial map
    static $init_latitude = 52.23;
    static $init_longitude = 21.01;

    // MySQL config
    static $dbhost = ""; // mysql host, eg. localhost
    static $dbuser = ""; // database user
    static $dbpass = ""; // database pass
    static $dbname = ""; // database name
    static $dbprefix = ""; // optional table names prefix, eg. "ulogger_"

    // require login/password authentication
    static $require_authentication = true;

    // all users tracks are visible to authenticated user
    static $public_tracks = false;

    // admin user who has access to all users locations
    // none if empty
    static $admin_user = "";

    // miniumum required length of user password
    static $pass_lenmin = 12;

    // required strength of user password
    //   0 = no requirements,
    //   1 = require mixed case letters (lower and upper),
    //   2 = require mixed case and numbers
    //   3 = require mixed case, numbers and non-alphanumeric characters
    static $pass_strength = 2;

    // Default interval in seconds for live auto reload
    static $interval = 10;

    // Default language
    static $lang = "en";

    // units
    static $units = "metric";

    private static $fileLoaded = false;

    private static $initialized = false;

   /**
    * Static initializer
    */
    static public function init() {
      if (!self::$initialized) {
        self::setFromFile();
        self::setFromCookies();
        self::$initialized = true;
      }
    }

   /**
    * Read config values from "/config.php" file
    */
    private static function setFromFile() {
      $configFile = ROOT_DIR . "/config.php";
      if (self::$fileLoaded || !file_exists($configFile)) {
        return;
      }
      self::$fileLoaded = true;
      include_once($configFile);

      if (isset($mapapi)) { self::$mapapi = $mapapi; }
      if (isset($gkey)) { self::$gkey = $gkey; }
      if (isset($ol_layers)) { self::$ol_layers = $ol_layers; }
      if (isset($init_latitude)) { self::$init_latitude = $init_latitude; }
      if (isset($init_longitude)) { self::$init_longitude = $init_longitude; }
      if (isset($dbhost)) { self::$dbhost = $dbhost; }
      if (isset($dbuser)) { self::$dbuser = $dbuser; }
      if (isset($dbpass)) { self::$dbpass = $dbpass; }
      if (isset($dbname)) { self::$dbname = $dbname; }
      if (isset($dbprefix)) { self::$dbprefix = $dbprefix; }
      if (isset($require_authentication)) { self::$require_authentication = (bool) $require_authentication; }
      if (isset($public_tracks)) { self::$public_tracks = (bool) $public_tracks; }
      if (isset($admin_user)) { self::$admin_user = $admin_user; }
      if (isset($pass_lenmin)) { self::$pass_lenmin = (int) $pass_lenmin; }
      if (isset($pass_strength)) { self::$pass_strength = (int) $pass_strength; }
      if (isset($interval)) { self::$interval = (int) $interval; }
      if (isset($lang)) { self::$lang = $lang; }
      if (isset($units)) { self::$units = $units; }

      if (!self::$require_authentication) {
        // tracks must be public if we don't require authentication
        self::$public_tracks = true;
      }
    }

   /**
    * Read config values stored in cookies
    */
    private static function setFromCookies() {
      if (isset($_COOKIE["ulogger_api"])) { self::$mapapi = $_COOKIE["ulogger_api"]; }
      if (isset($_COOKIE["ulogger_lang"])) { self::$lang = $_COOKIE["ulogger_lang"]; }
      if (isset($_COOKIE["ulogger_units"])) { self::$units = $_COOKIE["ulogger_units"]; }
      if (isset($_COOKIE["ulogger_interval"])) { self::$interval = $_COOKIE["ulogger_interval"]; }
    }

   /**
    * Is config loaded from file?
    *
    * @return True if loaded, false otherwise
    */
    public static function isFileLoaded() {
      return self::$fileLoaded;
    }

   /**
    * Regex to test if password matches strength and length requirements.
    * Valid for both php and javascript
    */
    public static function passRegex() {
      $regex = "";
      if (self::$pass_strength > 0) {
        // lower and upper case
        $regex .= "(?=.*[a-z])(?=.*[A-Z])";
      }
      if (self::$pass_strength > 1) {
        // digits
        $regex .= "(?=.*[0-9])";
      }
      if (self::$pass_strength > 2) {
        // not latin, not digits
        $regex .= "(?=.*[^a-zA-Z0-9])";
      }
      if (self::$pass_lenmin > 0) {
        $regex .= "(?=.{" . self::$pass_lenmin . ",})";
      }
      if (empty($regex)) {
        $regex = ".*";
      }
      return "/" . $regex . "/";
    }
  }

?>