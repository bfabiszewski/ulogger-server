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
  require_once(ROOT_DIR . "/helpers/config.php");
  require_once(ROOT_DIR . "/helpers/db.php");
  require_once(ROOT_DIR . "/helpers/track.php");
  require_once(ROOT_DIR . "/helpers/position.php");

  // for PHP 5.4 uncomment following line to include password_compat library
  //require_once(ROOT_DIR . "/helpers/password.php");

 /**
  * User handling routines
  */
  class uUser {
    public $id;
    public $login;
    public $hash;
    public $isAdmin = false;
    public $isValid = false;

    private static $db = null;

   /**
    * Constructor
    *
    * @param string $login Login
    */
    public function __construct($login = NULL) {
      if (!empty($login)) {
        $sql = "SELECT id, login, password FROM `" . self::db()->table('users') . "` WHERE login = ? LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $stmt->bind_result($this->id, $this->login, $this->hash);
        if ($stmt->fetch()) {
          $this->isValid = true;
        }
        $stmt->close();
        $this->isAdmin = self::isAdmin($this->login);
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
    * Add new user
    *
    * @param string $login Login
    * @param string $pass Password
    * @return int|bool New user id, false on error
    */
    public static function add($login, $pass) {
      $userid = false;
      if (!empty($login) && !empty($pass) && self::validPassStrength($pass)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO `" . self::db()->table('users') . "` (login, password) VALUES (?, ?)";
        $stmt = self::db()->prepare($sql);
        $stmt->bind_param('ss', $login, $hash);
        $stmt->execute();
        if (!self::db()->error && !$stmt->errno) {
          $userid = self::db()->insert_id;
        }
        $stmt->close();
      }
      return $userid;
    }

   /**
    * Delete user
    * This will also delete all user's positions and tracks
    *
    * @return bool True if success, false otherwise
    */
    public function delete() {
      $ret = false;
      if ($this->isValid) {
        // remove tracks and positions
        if (uTrack::deleteAll($this->id) === false) {
          return false;
        }
        // remove user
        $sql = "DELETE FROM `" . self::db()->table('users') . "` WHERE id = ?";
        $stmt = self::db()->prepare($sql);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        if (!self::db()->error && !$stmt->errno) {
          $ret = true;
          $this->id = NULL;
          $this->login = NULL;
          $this->hash = NULL;
          $this->isValid = false;
          $this->isAdmin = false;
        }
        $stmt->close();
      }
      return $ret;
    }

   /**
    * Set user password
    *
    * @param string $pass Password
    * @return bool True on success, false otherwise
    */
    public function setPass($pass) {
      $ret = false;
      if (!empty($this->login) && !empty($pass) && self::validPassStrength($pass)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "UPDATE `" . self::db()->table('users') . "` SET password = ? WHERE login = ?";
        $stmt = self::db()->prepare($sql);
        $stmt->bind_param('ss', $hash, $this->login);
        $stmt->execute();
        if (!self::db()->error && !$stmt->errno) {
          $ret = true;
        }
        $stmt->close();
      }
      return $ret;
    }

   /**
    * Check if given password matches user's one
    *
    * @param String $password Password
    * @return bool True if matches, false otherwise
    */
    public function validPassword($password) {
      return password_verify($password, $this->hash);
    }

   /**
    * Check if given password matches user's one
    *
    * @param String $password Password
    * @return bool True if matches, false otherwise
    */
    private static function validPassStrength($password) {
      return preg_match(uConfig::passRegex(), $password);
    }

   /**
    * Store uUser object in session
    */
    public function storeInSession() {
      $_SESSION['user'] = $this;
    }

   /**
    * Fill uUser object properties from session data
    * @return uUser
    */
    public function getFromSession() {
      if (isset($_SESSION['user'])) {
        $sessionUser = $_SESSION['user'];
        $this->id = $sessionUser->id;
        $this->login = $sessionUser->login;
        $this->hash = $sessionUser->hash;
        $this->isAdmin = $sessionUser->isAdmin;
        $this->isValid = $sessionUser->isValid;
      }
      return $this;
    }

   /**
    * Get all users
    *
    * @return array|bool Array of uUser users, false on error
    */
    public static function getAll() {
      $query = "SELECT id, login, password FROM `" . self::db()->table('users') . "` ORDER BY login";
      $result = self::db()->query($query);
      if ($result === false) {
        return false;
      }
      $userArr = [];
      while ($row = $result->fetch_assoc()) {
        $userArr[] = self::rowToObject($row);
      }
      $result->close();
      return $userArr;
    }

   /**
    * Convert database row to uUser
    *
    * @param array $row Row
    * @return uUser User
    */
    private static function rowToObject($row) {
      $user = new uUser();
      $user->id = $row['id'];
      $user->login = $row['login'];
      $user->hash = $row['password'];
      $user->isAdmin = self::isAdmin($row['login']);
      $user->isValid = true;
      return $user;
    }

   /**
    * Is given login admin user
    *
    * @param string $login Login
    * @return bool True if admin, false otherwise
    */
    private static function isAdmin($login) {
      return (!empty(uConfig::$admin_user) && uConfig::$admin_user == $login);
    }
  }
?>
