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

require_once(ROOT_DIR . "/helpers/db.php");
require_once(ROOT_DIR . "/helpers/layer.php");

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
  public static $mapApi = "openlayers";

  /**
   * @var string|null Google maps key
   */
  public static $googleKey;

  /**
   * @var uLayer[] Openlayers extra map layers
   */
  public static $olLayers = [];

  /**
   * @var float Default latitude for initial map
   */
  public static $initLatitude = 52.23;
  /**
   * @var float Default longitude for initial map
   */
  public static $initLongitude = 21.01;

  /**
   * @var string Database DSN
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
  public static $requireAuthentication = true;

  /**
   * @var bool All users tracks are visible to authenticated user
   */
  public static $publicTracks = false;

  /**
   * @var int Miniumum required length of user password
   */
  public static $passLenMin = 10;

  /**
   * @var int Required strength of user password
   * 0 = no requirements,
   * 1 = require mixed case letters (lower and upper),
   * 2 = require mixed case and numbers
   * 3 = require mixed case, numbers and non-alphanumeric characters
   */
  public static $passStrength = 2;

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
      self::setFromDatabase();
      self::setFromCookies();
      self::$initialized = true;
    }
  }

  /**
   * Get db instance
   *
   * @return uDb instance
   */
  private static function db() {
    return uDb::getInstance();
  }

  /**
   * Read config values from database
   */
  public static function setFromDatabase() {
    try {
      $query = "SELECT map_api, latitude, longitude, google_key, require_auth, public_tracks, 
                       pass_lenmin, pass_strength, interval_seconds, lang, units, 
                       stroke_weight, stroke_color, stroke_opacity 
                FROM " . self::db()->table('config') . " LIMIT 1";
      $result = self::db()->query($query);
      $row = $result->fetch();
      if ($row) {
        if (!empty($row['map_api'])) { self::$mapApi = $row['map_api']; }
        if (is_numeric($row['latitude'])) { self::$initLatitude = $row['latitude']; }
        if (is_numeric($row['longitude'])) { self::$initLongitude = $row['longitude']; }
        if (!empty($row['google_key'])) { self::$googleKey = $row['google_key']; }
        if (is_numeric($row['require_auth']) || is_bool($row['require_auth'])) { self::$requireAuthentication = (bool) $row['require_auth']; }
        if (is_numeric($row['public_tracks']) || is_bool($row['public_tracks'])) { self::$publicTracks = (bool) $row['public_tracks']; }
        if (is_numeric($row['pass_lenmin'])) { self::$passLenMin = $row['pass_lenmin']; }
        if (is_numeric($row['pass_strength'])) { self::$passStrength = $row['pass_strength']; }
        if (is_numeric($row['interval_seconds'])) { self::$interval = $row['interval_seconds']; }
        if (!empty($row['lang'])) { self::$lang = $row['lang']; }
        if (!empty($row['units'])) { self::$units = $row['units']; }
        if (is_numeric($row['stroke_weight'])) { self::$strokeWeight = $row['stroke_weight']; }
        if (is_numeric($row['stroke_color'])) { self::$strokeColor = self::getColorAsHex($row['stroke_color']); }
        if (is_numeric($row['stroke_opacity'])) { self::$strokeOpacity = $row['stroke_opacity'] / 100; }
      }
      self::setLayersFromDatabase();
      if (!self::$requireAuthentication) {
        // tracks must be public if we don't require authentication
        self::$publicTracks = true;
      }
    } catch (PDOException $e) {
      // TODO: handle exception
      syslog(LOG_ERR, $e->getMessage());
      return;
    }
  }

  /**
   * Read config values from database
   * @throws PDOException
   */
  private static function setLayersFromDatabase() {
    self::$olLayers = [];
    $query = "SELECT id, name, url, priority FROM " . self::db()->table('ol_layers');
    $result = self::db()->query($query);
    while ($row = $result->fetch()) {
      self::$olLayers[] = new uLayer($row['id'], $row['name'], $row['url'], $row['priority']);
    }
  }

  /**
   * Read config values from "/config.php" file
   * @noinspection IssetArgumentExistenceInspection
   * @noinspection DuplicatedCode
   * @noinspection PhpIncludeInspection
   */
  private static function setFromFile() {
    $configFile = ROOT_DIR . "/config.php";
    if (self::$fileLoaded || !file_exists($configFile)) { return; }
    self::$fileLoaded = true;
    include_once($configFile);

    if (isset($dbdsn)) { self::$dbdsn = $dbdsn; }
    if (isset($dbuser)) { self::$dbuser = $dbuser; }
    if (isset($dbpass)) { self::$dbpass = $dbpass; }
    if (isset($dbprefix)) { self::$dbprefix = $dbprefix; }
  }

  /**
   * Read config values stored in cookies
   */
  private static function setFromCookies() {
    if (isset($_COOKIE["ulogger_api"])) { self::$mapApi = $_COOKIE["ulogger_api"]; }
    if (isset($_COOKIE["ulogger_lang"])) { self::$lang = $_COOKIE["ulogger_lang"]; }
    if (isset($_COOKIE["ulogger_units"])) { self::$units = $_COOKIE["ulogger_units"]; }
    if (isset($_COOKIE["ulogger_interval"])) { self::$interval = $_COOKIE["ulogger_interval"]; }
  }

  /**
   * Is config loaded from file?
   *
   * @return bool True if loaded, false otherwise
   */
  public static function isFileLoaded() {
    return self::$fileLoaded;
  }

  /**
   * Regex to test if password matches strength and length requirements.
   * Valid for both php and javascript
   * @return string
   */
  public static function passRegex() {
    $regex = "";
    if (self::$passStrength > 0) {
      // lower and upper case
      $regex .= "(?=.*[a-z])(?=.*[A-Z])";
    }
    if (self::$passStrength > 1) {
      // digits
      $regex .= "(?=.*[0-9])";
    }
    if (self::$passStrength > 2) {
      // not latin, not digits
      $regex .= "(?=.*[^a-zA-Z0-9])";
    }
    if (self::$passLenMin > 0) {
      $regex .= "(?=.{" . self::$passLenMin . ",})";
    }
    if (empty($regex)) {
      $regex = ".*";
    }
    return "/" . $regex . "/";
  }

  /**
   * @param int $color Color value as integer
   * @return string Color hex string
   */
  private static function getColorAsHex($color) {
    return '#' . sprintf('%03x', $color);
  }

  /**
   * @param string $color Color hex string
   * @return int Color value as integer
   */
  private static function getColorAsInt($color) {
    return hexdec(str_replace('#', '', $color));
  }
}

?>
