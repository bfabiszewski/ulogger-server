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
  require_once(ROOT_DIR . "/helpers/position.php");

 /**
  * Track handling
  */
  class uTrack {
    public $id;
    public $userId;
    public $name;
    public $comment;

    public $isValid = false;

   /**
    * Constructor
    *
    * @param int $trackId Track id
    */
    public function __construct($trackId = NULL) {

      if (!empty($trackId)) {
        try {
          $query = "SELECT id, user_id, name, comment FROM " . self::db()->table('tracks') . " WHERE id = ? LIMIT 1";
          $stmt = self::db()->prepare($query);
          $stmt->execute([$trackId]);
          $stmt->bindColumn('id', $this->id, PDO::PARAM_INT);
          $stmt->bindColumn('user_id', $this->userId, PDO::PARAM_INT);
          $stmt->bindColumn('name', $this->name);
          $stmt->bindColumn('comment', $this->comment);
          if ($stmt->fetch(PDO::FETCH_BOUND)) {
            $this->isValid = true;
          }
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
      return uDb::getInstance();
    }

   /**
    * Add new track
    *
    * @param string $userId User id
    * @param string $name Name
    * @param string $comment Optional comment
    * @return int|bool New track id, false on error
    */
    public static function add($userId, $name, $comment = NULL) {
      $trackId = false;
      if (!empty($userId) && !empty($name)) {
        try {
          $table = self::db()->table('tracks');
          $query = "INSERT INTO $table (user_id, name, comment) VALUES (?, ?, ?)";
          $stmt = self::db()->prepare($query);
          $params = [ $userId, $name, $comment ];
          $stmt->execute($params);
          $trackId = (int) self::db()->lastInsertId("${table}_id_seq");
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $trackId;
    }

    /**
     * Add new position to track
     *
     * @param int $userId
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
    public function addPosition($userId, $timestamp, $lat, $lon,
                                $altitude = NULL, $speed = NULL, $bearing = NULL, $accuracy = NULL,
                                $provider = NULL, $comment = NULL, $imageId = NULL) {
      return uPosition::add($userId, $this->id, $timestamp, $lat, $lon,
                                   $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId);
    }

   /**
    * Delete track with all positions
    *
    * @return bool True if success, false otherwise
    */
    public function delete() {
      $ret = false;
      if ($this->isValid) {
        // delete positions
        if (uPosition::deleteAll($this->userId, $this->id) === false) {
          return false;
        }
        // delete track metadata
        try {
          $query = "DELETE FROM " . self::db()->table('tracks') . " WHERE id = ?";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $this->id ]);
          $ret = true;
          $this->id = NULL;
          $this->userId = NULL;
          $this->name = NULL;
          $this->comment = NULL;
          $this->isValid = false;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $ret;
    }

   /**
    * Update track
    *
    * @param string|null $name New name (not empty string) or NULL if not changed
    * @param string|null $comment New comment or NULL if not changed (to remove content use empty string: "")
    * @return bool True if success, false otherwise
    */
    public function update($name = NULL, $comment = NULL) {
      $ret = false;
      if (empty($name)) { $name = $this->name; }
      if (is_null($comment)) { $comment = $this->comment; }
      if ($comment === "") { $comment = NULL; }
      if ($this->isValid) {
        try {
          $query = "UPDATE " . self::db()->table('tracks') . " SET name = ?, comment = ? WHERE id = ?";
          $stmt = self::db()->prepare($query);
          $params = [ $name, $comment, $this->id ];
          $stmt->execute($params);
          $ret = true;
          $this->name = $name;
          $this->comment = $comment;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $ret;
    }

   /**
    * Delete all user's tracks
    *
    * @param string $userId User id
    * @return bool True if success, false otherwise
    */
    public static function deleteAll($userId) {
      $ret = false;
      if (!empty($userId) && uPosition::deleteAll($userId) === true) {
        // remove all tracks
        try {
          $query = "DELETE FROM " . self::db()->table('tracks') . " WHERE user_id = ?";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $userId ]);
          $ret = true;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $ret;
    }

   /**
    * Get all tracks
    *
    * @param int $userId Optional limit to user id
    * @return array|bool Array of uTrack tracks, false on error
    */
    public static function getAll($userId = NULL) {
      if (!empty($userId)) {
        $where = "WHERE user_id=" . self::db()->quote($userId);
      } else {
        $where = "";
      }
      $query = "SELECT id, user_id, name, comment FROM " . self::db()->table('tracks') . " $where ORDER BY id DESC";
      try {
        $result = self::db()->query($query);
        $trackArr = [];
        while ($row = $result->fetch()) {
          $trackArr[] = self::rowToObject($row);
        }
      } catch (PDOException $e) {
        // TODO: handle exception
        syslog(LOG_ERR, $e->getMessage());
        $trackArr = false;
      }
      return $trackArr;
    }

   /**
    * Convert database row to uTrack
    *
    * @param array $row Row
    * @return uTrack Track
    */
    private static function rowToObject($row) {
      $track = new uTrack();
      $track->id = $row['id'];
      $track->userId = $row['user_id'];
      $track->name = $row['name'];
      $track->comment = $row['comment'];
      $track->isValid = true;
      return $track;
    }
  }

?>