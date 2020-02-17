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
    /**
     * @var string Version number
     */
    public static $version = "1.0-beta";

    /**
     * @var string Default map drawing framework
     */
    public static $mapapi = "openlayers";

    /**
     * @var string|null Google maps key
     */
    public static $gkey;

    /**
     * @var array Openlayers additional map layers
     */
    public static $ol_layers = [];

    /**
     * @var float Default latitude for initial map
     */
    public static $init_latitude = 52.23;
    /**
     * @var float Default longitude for initial map
     */
    public static $init_longitude = 21.01;

    /**
     * @var string Database dsn
     */
    public static $dbdsn = "";
    /**
     * @var string Database user
     */
    public static $dbuser = "";
    /**
     * @var string Database pass
     */
    public static $dbpass = "";
    /**
     * @var string Optional table names prefix, eg. "ulogger_"
     */
    public static $dbprefix = "";

    /**
     * @var bool Require login/password authentication
     */
    public static $require_authentication = true;

    /**
     * @var bool All users tracks are visible to authenticated user
     */
    public static $public_tracks = false;

    /**
     * @var string Admin user who has access to all users locations
     * none if empty
     */
    public static $admin_user = "";

    /**
     * @var int Miniumum required length of user password
     */
    public static $pass_lenmin = 12;

    /**
     * @var int Required strength of user password
     * 0 = no requirements,
     * 1 = require mixed case letters (lower and upper),
     * 2 = require mixed case and numbers
     * 3 = require mixed case, numbers and non-alphanumeric characters
     */
    public static $pass_strength = 2;

    /**
     * @var int Default interval in seconds for live auto reload
     */
    public static $interval = 10;

    /**
     * @var string Default language code
     */
    public static $lang = "en";

    /**
     * @var string Default units
     */
    public static $units = "metric";

    /**
     * @var int Stroke weight
     */
    public static $strokeWeight = 2;
    /**
     * @var string Stroke color
     */
    public static $strokeColor = '#ff0000';
    /**
     * @var int Stroke opacity
     */
    public static $strokeOpacity = 1;

    private static $fileLoaded = false;

    private static $initialized = false;

   /**
    * Static initializer
    */
    public static function init() {
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
      if (isset($gkey) && !empty($gkey)) { self::$gkey = $gkey; }
      if (isset($ol_layers)) { self::$ol_layers = $ol_layers; }
      if (isset($init_latitude)) { self::$init_latitude = $init_latitude; }
      if (isset($init_longitude)) { self::$init_longitude = $init_longitude; }
      if (isset($dbdsn)) { self::$dbdsn = $dbdsn; }
      if (isset($dbuser)) { self::$dbuser = $dbuser; }
      if (isset($dbpass)) { self::$dbpass = $dbpass; }
      if (isset($dbprefix)) { self::$dbprefix = $dbprefix; }
      if (isset($require_authentication)) { self::$require_authentication = (bool) $require_authentication; }
      if (isset($public_tracks)) { self::$public_tracks = (bool) $public_tracks; }
      if (isset($admin_user)) { self::$admin_user = $admin_user; }
      if (isset($pass_lenmin)) { self::$pass_lenmin = (int) $pass_lenmin; }
      if (isset($pass_strength)) { self::$pass_strength = (int) $pass_strength; }
      if (isset($interval)) { self::$interval = (int) $interval; }
      if (isset($lang)) { self::$lang = $lang; }
      if (isset($units)) { self::$units = $units; }
      if (isset($strokeWeight)) { self::$strokeWeight = $strokeWeight; }
      if (isset($strokeColor)) { self::$strokeColor = $strokeColor; }
      if (isset($strokeOpacity)) { self::$strokeOpacity = $strokeOpacity; }

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
