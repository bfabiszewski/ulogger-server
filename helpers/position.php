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

 /**
  * Positions handling
  */
  class uPosition {
    public $id;
    public $time;
    public $userId;
    public $userLogin;
    public $trackId;
    public $trackName;
    public $latitude;
    public $longitude;
    public $altitude;
    public $speed;
    public $bearing;
    public $accuracy;
    public $provider;
    public $comment; // not used yet
    public $imageId; // not used yet

    public $isValid = false;

    private static $db;

   /**
    * Constructor
    * @param integer $positionId Position id
    */
    public function __construct($positionId = NULL) {

      self::$db = uDB::getInstance();

      if (!empty($positionId)) {
        $query = "SELECT p.id, p.time, p.user_id, p.track_id,
                  p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                  p.comment, p.image_id, u.login, t.name
                  FROM positions p
                  LEFT JOIN users u ON (p.user_id = u.id)
                  LEFT JOIN tracks t ON (p.track_id = t.id)
                  WHERE id = ? LIMIT 1";
        $params = [ 'i', $positionId ];
        $this->loadWithQuery($query, $params);
      }
    }

   /**
    * Add position
    *
    * @param int $userId
    * @param int $trackId
    * @param int $time Unix time stamp
    * @param double $lat
    * @param double $lon
    * @param double $altitude
    * @param double $speed
    * @param double $bearing
    * @param int $accuracy
    * @param string $provider
    * @param string $comment
    * @param int $imageId
    * @return int|bool New position id in database, false on error
    */
    public function add($userId, $trackId, $time, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId) {
      $positionId = false;
      if (!is_null($lat) && !is_null($lon) && !is_null($time) && !empty($userId) && !empty($trackId)) {
        $query = "INSERT INTO positions
                (user_id, track_id,
                time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image_id)
                VALUES (?, ?, FROM_UNIXTIME(?), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($query);
        $stmt->bind_param('iisddddddssi',
                $userId, $trackId,
                $time, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId);
        $stmt->execute();
        if (!self::$db->error && !$stmt->errno) {
          $positionId = self::$db->insert_id;
        }
        $stmt->close();
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
    public function deleteAll($userId, $trackId = NULL) {
      $ret = false;
      if (!empty($userId)) {
        $args = [];
        $where = "WHERE user_id = ?";
        $args[0] = "i";
        $args[1] = &$userId;
        if (!empty($trackId)) {
          $where .= " AND track_id = ?";
          $args[0] .= "i";
          $args[2] = &$trackId;
        }
        $query = "DELETE FROM positions $where";
        $stmt = self::$db->prepare($query);
        call_user_func_array([ $stmt, 'bind_param' ], $args);
        $stmt->execute();
        if (!self::$db->error && !$stmt->errno) {
          $ret = true;
        }
        $stmt->close();
      }
      return $ret;
    }

   /**
    * Fill class properties with last position data from database
    * (for given user if specified)
    *
    * @param int $userId Optional user id
    * @return uPosition Self
    */
    public function getLast($userId = NULL) {
      if (!empty($userId)) {
        $where = "WHERE p.user_id = ?";
        $params = [ 'i', $userId ];
      } else {
        $where = "";
        $params = NULL;
      }
      $query = "SELECT p.id, p.time, p.user_id, p.track_id,
                p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                p.comment, p.image_id, u.login, t.name
                FROM positions p
                LEFT JOIN users u ON (p.user_id = u.id)
                LEFT JOIN tracks t ON (p.track_id = t.id)
                $where
                ORDER BY p.time DESC LIMIT 1";
      $this->loadWithQuery($query, $params);
      return $this;
    }

   /**
    * Get array of all positions
    *
    * @param int $userId Optional limit to given user id
    * @param int $trackId Optional limit to given track id
    * @return array|bool Array of uPosition positions, false on error
    */
    public function getAll($userId = NULL, $trackId = NULL) {
      $rules = [];
      if (!empty($userId)) {
        $rules[] = "p.user_id = '" . self::$db->real_escape_string($userId) ."'";
      }
      if (!empty($trackId)) {
        $rules[] = "p.track_id = '" . self::$db->real_escape_string($trackId) ."'";
      }
      if (!empty($rules)) {
        $where = "WHERE " . implode(" AND ", $rules);
      } else {
        $where = "";
      }
      $query = "SELECT p.id, p.time, p.user_id, p.track_id,
                p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.accuracy, p.provider,
                p.comment, p.image_id, u.login, t.name
                FROM positions p
                LEFT JOIN users u ON (p.user_id = u.id)
                LEFT JOIN tracks t ON (p.track_id = t.id)
                $where
                ORDER BY p.time";
      $result = self::$db->query($query);
      if ($result === false) {
        return false;
      }
      $positionsArr = [];
      while ($row = $result->fetch_assoc()) {
        $positionsArr[] = $this->rowToObject($row);
      }
      $result->close();
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
      return strtotime($this->time) - strtotime($target->time);
    }

   /**
    * Convert database row to uPosition
    *
    * @param array $row Row
    * @return uPosition Position
    */
    private function rowToObject($row) {
      $position = new uPosition();
      $position->id = $row['id'];
      $position->time = $row['time'];
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
    * @param array|null $bindParams Optional array of bind parameters (types, params)
    */
    private function loadWithQuery($query, $bindParams = NULL) {
      $stmt = self::$db->prepare($query);
      if (is_array($bindParams) && ($types = array_shift($bindParams))) {
        call_user_func_array(
            [ $stmt, 'bind_param' ],
            array_merge([ $types ], array_map(function(&$param) { return $param; }, $bindParams))
        );
      }
      if ($stmt->execute()) {
        $stmt->bind_result($this->id, $this->time, $this->userId, $this->trackId,
                            $this->latitude, $this->longitude, $this->altitude, $this->speed,
                            $this->bearing, $this->accuracy, $this->provider,
                            $this->comment, $this->imageId, $this->userLogin, $this->trackName);
        if ($stmt->fetch()) {
          $this->isValid = true;
        }
      }
      $stmt->close();
    }
  }

?>