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
 * Handles config values
 */
class uConfig {
  /**
   * Singleton instance
   *
   * @var uConfig Object instance
   */
  private static $instance;
  /**
   * @var string Version number
   */
  public $version = "1.0-beta";

  /**
   * @var string Default map drawing framework
   */
  public $mapApi = "openlayers";

  /**
   * @var string|null Google maps key
   */
  public $googleKey;

  /**
   * @var uLayer[] Openlayers extra map layers
   */
  public $olLayers = [];

  /**
   * @var float Default latitude for initial map
   */
  public $initLatitude = 52.23;
  /**
   * @var float Default longitude for initial map
   */
  public $initLongitude = 21.01;

  /**
   * @var bool Require login/password authentication
   */
  public $requireAuthentication = true;

  /**
   * @var bool All users tracks are visible to authenticated user
   */
  public $publicTracks = false;

  /**
   * @var int Miniumum required length of user password
   */
  public $passLenMin = 10;

  /**
   * @var int Required strength of user password
   * 0 = no requirements,
   * 1 = require mixed case letters (lower and upper),
   * 2 = require mixed case and numbers
   * 3 = require mixed case, numbers and non-alphanumeric characters
   */
  public $passStrength = 2;

  /**
   * @var int Default interval in seconds for live auto reload
   */
  public $interval = 10;

  /**
   * @var string Default language code
   */
  public $lang = "en";

  /**
   * @var string Default units
   */
  public $units = "metric";

  /**
   * @var int Stroke weight
   */
  public $strokeWeight = 2;
  /**
   * @var string Stroke color
   */
  public $strokeColor = '#ff0000';
  /**
   * @var float Stroke opacity
   */
  public $strokeOpacity = 1.0;
  
  public function __construct($useDatabase = true) {
    if ($useDatabase) {
      $this->setFromDatabase();
    }
    $this->setFromCookies();
  }

  /**
   * Returns singleton instance
   *
   * @return uConfig Singleton instance
   */
  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Returns singleton instance
   *
   * @return uConfig Singleton instance
   */
  public static function getOfflineInstance() {
    if (!self::$instance) {
      self::$instance = new self(false);
    }
    return self::$instance;
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
  public function setFromDatabase() {
    try {
      $query = "SELECT map_api, latitude, longitude, google_key, require_auth, public_tracks, 
                       pass_lenmin, pass_strength, interval_seconds, lang, units, 
                       stroke_weight, stroke_color, stroke_opacity 
                FROM " . self::db()->table('config') . " LIMIT 1";
      $result = self::db()->query($query);
      $row = $result->fetch();
      if ($row) {
        if (!empty($row['map_api'])) { $this->mapApi = $row['map_api']; }
        if (is_numeric($row['latitude'])) { $this->initLatitude = (float) $row['latitude']; }
        if (is_numeric($row['longitude'])) { $this->initLongitude = (float) $row['longitude']; }
        if (!empty($row['google_key'])) { $this->googleKey = $row['google_key']; }
        if (is_numeric($row['require_auth']) || is_bool($row['require_auth'])) { $this->requireAuthentication = (bool) $row['require_auth']; }
        if (is_numeric($row['public_tracks']) || is_bool($row['public_tracks'])) { $this->publicTracks = (bool) $row['public_tracks']; }
        if (is_numeric($row['pass_lenmin'])) { $this->passLenMin = (int) $row['pass_lenmin']; }
        if (is_numeric($row['pass_strength'])) { $this->passStrength = (int) $row['pass_strength']; }
        if (is_numeric($row['interval_seconds'])) { $this->interval = (int) $row['interval_seconds']; }
        if (!empty($row['lang'])) { $this->lang = $row['lang']; }
        if (!empty($row['units'])) { $this->units = $row['units']; }
        if (is_numeric($row['stroke_weight'])) { $this->strokeWeight = (int) $row['stroke_weight']; }
        if (is_numeric($row['stroke_color'])) { $this->strokeColor = self::getColorAsHex($row['stroke_color']); }
        if (is_numeric($row['stroke_opacity'])) { $this->strokeOpacity = $row['stroke_opacity'] / 100; }
      }
      $this->setLayersFromDatabase();
      if (!$this->requireAuthentication) {
        // tracks must be public if we don't require authentication
        $this->publicTracks = true;
      }
    } catch (PDOException $e) {
      // TODO: handle exception
      syslog(LOG_ERR, $e->getMessage());
    }
  }

  /**
   * Save config values to database
   * @return bool True on success, false otherwise
   */
  public function save() {
    $ret = false;
    try {
      $query = "UPDATE " . self::db()->table('config') . "
                SET map_api = ?, latitude = ?, longitude = ?, google_key = ?, require_auth = ?, public_tracks = ?, 
                    pass_lenmin = ?, pass_strength = ?, interval_seconds = ?, lang = ?, units = ?, 
                    stroke_weight = ?, stroke_color = ?, stroke_opacity = ?";
      $stmt = self::db()->prepare($query);
      $params = [
        $this->mapApi,
        $this->initLatitude,
        $this->initLongitude,
        $this->googleKey,
        (int) $this->requireAuthentication,
        (int) $this->publicTracks,
        $this->passLenMin,
        $this->passStrength,
        $this->interval,
        $this->lang,
        $this->units,
        $this->strokeWeight,
        self::getColorAsInt($this->strokeColor),
        (int) ($this->strokeOpacity * 100)
      ];
      $stmt->execute($params);
      $this->saveLayers();
      $ret = true;
    } catch (PDOException $e) {
      // TODO: handle exception
      syslog(LOG_ERR, $e->getMessage());
    }
    return $ret;
  }

  /**
   * Truncate ol_layers table
   * @throws PDOException
   */
  private function deleteLayers() {
    $query = "DELETE FROM " . self::db()->table('ol_layers');
    self::db()->exec($query);
  }

  /**
   * Save layers to database
   * @throws PDOException
   */
  private function saveLayers() {
    $this->deleteLayers();
    if (!empty($this->olLayers)) {
      $query = "INSERT INTO " . self::db()->table('ol_layers') . " (id, name, url, priority) VALUES (?, ?, ?, ?)";
      $stmt = self::db()->prepare($query);
      foreach ($this->olLayers as $layer) {
        $stmt->execute([ $layer->id, $layer->name, $layer->url, $layer->priority]);
      }
    }
  }

  /**
   * Read config values from database
   * @throws PDOException
   */
  private function setLayersFromDatabase() {
    $this->olLayers = [];
    $query = "SELECT id, name, url, priority FROM " . self::db()->table('ol_layers');
    $result = self::db()->query($query);
    while ($row = $result->fetch()) {
      $this->olLayers[] = new uLayer($row['id'], $row['name'], $row['url'], $row['priority']);
    }
  }

  /**
   * Read config values stored in cookies
   */
  private function setFromCookies() {
    if (isset($_COOKIE["ulogger_api"])) { $this->mapApi = $_COOKIE["ulogger_api"]; }
    if (isset($_COOKIE["ulogger_lang"])) { $this->lang = $_COOKIE["ulogger_lang"]; }
    if (isset($_COOKIE["ulogger_units"])) { $this->units = $_COOKIE["ulogger_units"]; }
    if (isset($_COOKIE["ulogger_interval"])) { $this->interval = $_COOKIE["ulogger_interval"]; }
  }


  /**
   * Check if given password matches user's one
   *
   * @param String $password Password
   * @return bool True if matches, false otherwise
   */
  public function validPassStrength($password) {
    return preg_match($this->passRegex(), $password);
  }

  /**
   * Regex to test if password matches strength and length requirements.
   * Valid for both php and javascript
   * @return string
   */
  public function passRegex() {
    $regex = "";
    if ($this->passStrength > 0) {
      // lower and upper case
      $regex .= "(?=.*[a-z])(?=.*[A-Z])";
    }
    if ($this->passStrength > 1) {
      // digits
      $regex .= "(?=.*[0-9])";
    }
    if ($this->passStrength > 2) {
      // not latin, not digits
      $regex .= "(?=.*[^a-zA-Z0-9])";
    }
    if ($this->passLenMin > 0) {
      $regex .= "(?=.{" . $this->passLenMin . ",})";
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
