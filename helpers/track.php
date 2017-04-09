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

  require_once(__DIR__ . "/db.php");

 /**
  * Track handling
  */
  class uTrack {
    public $id;
    public $userId;
    public $name;
    public $comment;

    public $isValid = false;

    private static $db;

   /**
    * Constructor
    *
    * @param int $trackId Track id
    */
    public function __construct($trackId = NULL) {

      self::$db = uDB::getInstance();

      if (!empty($trackId)) {
        $stmt = self::$db->prepare("SELECT id, user_id, name, comment FROM tracks WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $trackId);
        $stmt->execute();
        $stmt->bind_result($this->id, $this->userId, $this->name, $this->comment);
        if ($stmt->fetch()) {
          $this->isValid = true;
        }
        $stmt->close();

      }
    }

   /**
    * Add new track
    *
    * @param string $userId User id
    * @param string $name Name
    * @param string $comment Optional comment
    * @return int|bool New track id, false on error
    */
    public function add($userId, $name, $comment = NULL) {
      $trackId = false;
      if (!empty($userId) && !empty($name)) {
        $query = "INSERT INTO tracks (user_id, name, comment) VALUES (?, ?, ?)";
        $stmt = self::$db->prepare($query);
        $stmt->bind_param('iss', $userId, $name, $comment);
        $stmt->execute();
        if (!self::$db->error && !$stmt->errno) {
          $trackId = self::$db->insert_id;
        }
        $stmt->close();
      }
      return $trackId;
    }

   /**
    * Get all tracks
    *
    * @param int $userId Optional limit to user id
    * @return array|bool Array of uTrack tracks, false on error
    */
    public function getAll($userId = NULL) {
      if (!empty($userId)) {
        $where = "WHERE user_id='" . self::$db->real_escape_string($userId) ."'";
      } else {
        $where = "";
      }
      $query = "SELECT id, user_id, name, comment FROM tracks $where ORDER BY id DESC";
      $result = self::$db->query($query);
      if ($result === false) {
        return false;
      }
      $trackArr = [];
      while ($row = $result->fetch_assoc()) {
        $trackArr[] = $this->rowToObject($row);
      }
      $result->close();
      return $trackArr;
    }

   /**
    * Convert database row to uTrack
    *
    * @param array $row Row
    * @return uTrack Track
    */
    private function rowToObject($row) {
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