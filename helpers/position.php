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
  require_once(ROOT_DIR . "/helpers/track.php");

 /**
  * Positions handling
  */
  class uPosition {
    /** @param int Position id */
    public $id;
    /** @param int Unix time stamp */
    public $timestamp;
    /** @param int User id */
    public $userId;
    /** @param String User login */
    public $userLogin;
    /** @param int Track id */
    public $trackId;
    /** @param String Track name */
    public $trackName;
    /** @param double Latitude */
    public $latitude;
    /** @param double Longitude */
    public $longitude;
    /** @param double Altitude */
    public $altitude;
    /** @param double Speed */
    public $speed;
    /** @param double Bearing */
    public $bearing;
    /** @param int Accuracy */
    public $accuracy;
    /** @param String Provider */
    public $provider;
    /** @param String Comment */
    public $comment; // not used yet
    /** @param int Image id */
    public $imageId; // not used yet

    public $isValid = false;

    private static $db;

   /**
    * Constructor
    * @param integer $positionId Position id
    */
    public function __construct($positionId = NULL) {

      if (!empty($positionId)) {
        $query = "SELECT p.id, " . self::db()->unix_timestamp('p.time') . " AS tstamp, p.user_id, p.track_id,
                  p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                  p.comment, p.image_id, u.login, t.name
                  FROM " . self::db()->table('positions') . " p
                  LEFT JOIN " . self::db()->table('users') . " u ON (p.user_id = u.id)
                  LEFT JOIN " . self::db()->table('tracks') . " t ON (p.track_id = t.id)
                  WHERE id = ? LIMIT 1";
        $params = [ $positionId ];
        try {
          $this->loadWithQuery($query, $params);
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
    }

    /**
     * Get db instance
     *
     * @return uDb instance
     */
    private static function db() {
      if (is_null(self::$db)) {
        self::$db = uDb::getInstance();
      }
      return self::$db;
    }

   /**
    * Add position
    *
    * @param int $userId
    * @param int $trackId
    * @param int $timestamp Unix time stamp
    * @param double $lat
    * @param double $lon
    * @param double $altitude Optional
    * @param double $speed Optional
    * @param double $bearing Optional
    * @param int $accuracy Optional
    * @param string $provider Optional
    * @param string $comment Optional
    * @param int $imageId Optional
    * @return int|bool New position id in database, false on error
    */
    public static function add($userId, $trackId, $timestamp, $lat, $lon,
                               $altitude = NULL, $speed = NULL, $bearing = NULL, $accuracy = NULL,
                               $provider = NULL, $comment = NULL, $imageId = NULL) {
      $positionId = false;
      if (is_numeric($lat) && is_numeric($lon) && is_numeric($timestamp) && is_numeric($userId) && is_numeric($trackId)) {
        $track = new uTrack($trackId);
        if ($track->isValid && $track->userId == $userId) {
          try {
            $table = self::db()->table('positions');
            $query = "INSERT INTO $table
                      (user_id, track_id,
                      time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image_id)
                      VALUES (?, ?, " . self::db()->from_unixtime('?') . ", ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = self::db()->prepare($query);
            $params = [ $userId, $trackId,
                    $timestamp, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId ];
            $stmt->execute($params);
            $positionId = self::db()->lastInsertId("${table}_id_seq");
          } catch (PDOException $e) {
            // TODO: handle error
            syslog(LOG_ERR, $e->getMessage());
          }
        }
      }
      return $positionId;
    }

   /**
    * Delete all user's positions, optionally limit to given track
    *
    * @param int $userId User id
    * @param int $trackId Optional track id
    * @return bool True if success, false otherwise
    */
    public static function deleteAll($userId, $trackId = NULL) {
      $ret = false;
      if (!empty($userId)) {
        $args = [];
        $where = "WHERE user_id = ?";
        $args[] = $userId;
        if (!empty($trackId)) {
          $where .= " AND track_id = ?";
          $args[] = $trackId;
        }
        try {
          $query = "DELETE FROM " . self::db()->table('positions') . " $where";
          $stmt = self::db()->prepare($query);
          $stmt->execute($args);
          $ret = true;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $ret;
    }

   /**
    * Get last position data from database
    * (for given user if specified)
    *
    * @param int $userId Optional user id
    * @return uPosition Position
    */
    public static function getLast($userId = NULL) {
      if (!empty($userId)) {
        $where = "WHERE p.user_id = ?";
        $params = [ $userId ];
      } else {
        $where = "";
        $params = NULL;
      }
      $query = "SELECT p.id, " . self::db()->unix_timestamp('p.time') . " AS tstamp, p.user_id, p.track_id,
                p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                p.comment, p.image_id, u.login, t.name
                FROM " . self::db()->table('positions') . " p
                LEFT JOIN " . self::db()->table('users') . " u ON (p.user_id = u.id)
                LEFT JOIN " . self::db()->table('tracks') . " t ON (p.track_id = t.id)
                $where
                ORDER BY p.time DESC, p.id DESC LIMIT 1";
      $position = new uPosition();
      try {
        $position->loadWithQuery($query, $params);
      } catch (PDOException $e) {
        // TODO: handle exception
        syslog(LOG_ERR, $e->getMessage());
      }
      return $position;
    }

    /**
    * Get last position data from database
    * (for all users)
    *
    * @return array|bool Array of uPosition positions, false on error
    */
    public static function getLastAllUsers() {
      $query = "SELECT 
                  p.id, 
                  UNIX_TIMESTAMP(p.time) AS tstamp, 
                  p.user_id, 
                  p.track_id, 
                  p.latitude, 
                  p.longitude, 
                  p.altitude, 
                  p.speed, 
                  p.bearing, 
                  p.accuracy, 
                  p.provider, 
                  p.comment, 
                  p.image_id, 
                  u.login
                FROM   
                  " . self::db()->table('positions') . " p 
                LEFT JOIN " . self::db()->table('users') . " u 
                  ON ( p.user_id = u.id ) 
                WHERE  p.id = (
                        SELECT 
                          p2.id 
                        FROM   
                          " . self::db()->table('positions') . " p2 
                        WHERE  
                          p2.user_id = p.user_id 
                        ORDER BY 
                          p2.time DESC, 
                          p2.id DESC 
                        LIMIT  1
                )";

      $result = self::db()->query($query);
      if ($result === false) {
        return false;
      }
      $positionsArr = [];
      while ($row = $result->fetch_assoc()) {
        $positionsArr[] = self::rowToObject($row);
      }
      $result->close();
      return $positionsArr;
    }

   /**
    * Get array of all positions
    *
    * @param int $userId Optional limit to given user id
    * @param int $trackId Optional limit to given track id
    * @return array|bool Array of uPosition positions, false on error
    */
    public static function getAll($userId = NULL, $trackId = NULL) {
      $rules = [];
      if (!empty($userId)) {
        $rules[] = "p.user_id = " . self::db()->quote($userId);
      }
      if (!empty($trackId)) {
        $rules[] = "p.track_id = " . self::db()->quote($trackId);
      }
      if (!empty($rules)) {
        $where = "WHERE " . implode(" AND ", $rules);
      } else {
        $where = "";
      }
      $query = "SELECT p.id, " . self::db()->unix_timestamp('p.time') . " AS tstamp, p.user_id, p.track_id,
                p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                p.comment, p.image_id, u.login, t.name
                FROM " . self::db()->table('positions') . " p
                LEFT JOIN " . self::db()->table('users') . " u ON (p.user_id = u.id)
                LEFT JOIN " . self::db()->table('tracks') . " t ON (p.track_id = t.id)
                $where
                ORDER BY p.time, p.id";
      try {
        $positionsArr = [];
        $result = self::db()->query($query);
        while ($row = $result->fetch()) {
          $positionsArr[] = self::rowToObject($row);
        }
      } catch (PDOException $e) {
        // TODO: handle exception
        syslog(LOG_ERR, $e->getMessage());
      }
      return $positionsArr;
    }

   /**
    * Calculate distance to target point using haversine formula
    *
    * @param uPosition $target Target position
    * @return int Distance in meters
    */
    public function distanceTo($target) {
      $lat1 = deg2rad($this->latitude);
      $lon1 = deg2rad($this->longitude);
      $lat2 = deg2rad($target->latitude);
      $lon2 = deg2rad($target->longitude);
      $latD = $lat2 - $lat1;
      $lonD = $lon2 - $lon1;
      $bearing = 2 * asin(sqrt(pow(sin($latD / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lonD / 2), 2)));
      return $bearing * 6371000;
    }

   /**
    * Calculate time elapsed since target point
    *
    * @param uPosition $target Target position
    * @return int Number of seconds
    */
    public function secondsTo($target) {
      return $this->timestamp - $target->timestamp;
    }

   /**
    * Convert database row to uPosition
    *
    * @param array $row Row
    * @return uPosition Position
    */
    private static function rowToObject($row) {
      $position = new uPosition();
      $position->id = $row['id'];
      $position->timestamp = $row['tstamp'];
      $position->userId = $row['user_id'];
      $position->userLogin = $row['login'];
      $position->trackId = $row['track_id'];
      $position->trackName = $row['name'];
      $position->latitude = $row['latitude'];
      $position->longitude = $row['longitude'];
      $position->altitude = $row['altitude'];
      $position->speed = $row['speed'];
      $position->bearing = $row['bearing'];
      $position->accuracy = $row['accuracy'];
      $position->provider = $row['provider'];
      $position->comment = $row['comment'];
      $position->imageId = $row['image_id'];
      $position->isValid = true;
      return $position;
    }

   /**
    * Fill class properties with database query result
    *
    * @param string $query Query
    * @param array|null $params Optional array of bind parameters
    * @throws PDOException
    */
    private function loadWithQuery($query, $params = NULL) {
      $stmt = self::db()->prepare($query);
      $stmt->execute($params);

      $stmt->bindColumn('id', $this->id, PDO::PARAM_INT);
      $stmt->bindColumn('tstamp', $this->timestamp, PDO::PARAM_INT);
      $stmt->bindColumn('user_id', $this->userId, PDO::PARAM_INT);
      $stmt->bindColumn('track_id', $this->trackId, PDO::PARAM_INT);
      $stmt->bindColumn('latitude', $this->latitude);
      $stmt->bindColumn('longitude', $this->longitude);
      $stmt->bindColumn('altitude', $this->altitude);
      $stmt->bindColumn('speed', $this->speed);
      $stmt->bindColumn('bearing', $this->bearing);
      $stmt->bindColumn('accuracy', $this->accuracy, PDO::PARAM_INT);
      $stmt->bindColumn('provider', $this->provider);
      $stmt->bindColumn('comment', $this->comment);
      $stmt->bindColumn('image_id', $this->imageId, PDO::PARAM_INT);
      $stmt->bindColumn('login', $this->userLogin);
      $stmt->bindColumn('name', $this->trackName);
      if ($stmt->fetch(PDO::FETCH_BOUND)) {
        $this->isValid = true;
      }
    }
  }

?>
