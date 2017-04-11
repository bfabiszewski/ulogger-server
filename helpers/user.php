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
  require_once (ROOT_DIR . "/helpers/config.php");
  require_once (ROOT_DIR . "/helpers/db.php");

 /**
  * User handling routines
  */
  class uUser {
    public $id;
    public $login;
    public $hash;
    public $isAdmin = false;
    public $isValid = false;

    private static $db;

   /**
    * Constructor
    *
    * @param string $login Login
    */
    public function __construct($login = NULL) {
      self::$db = uDB::getInstance();
      if (!empty($login)) {
        $stmt = self::$db->prepare("SELECT id, login, password FROM users WHERE login = ? LIMIT 1");
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $stmt->bind_result($this->id, $this->login, $this->hash);
        if ($stmt->fetch()) {
          $this->isValid = true;
        }
        $stmt->close();
        $this->isAdmin = $this->isAdmin($this->login);
      }
    }

   /**
    * Add new user
    *
    * @param string $login Login
    * @param string $hash Password hash
    * @return int|bool New user id, false on error
    */
    public function add($login, $hash) {
      $userid = false;
      if (!empty($login) && !empty($hash)) {
        $sql = "INSERT INTO users (login, password) VALUES (?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->bind_param('ss', $login, $hash);
        $stmt->execute();
        if (!self::$db->error && !$stmt->errno) {
          $userid = self::$db->insert_id;
        }
        $stmt->close();
      }
      return $userid;
    }

   /**
    * Set user password
    *
    * @param string $hash Hash
    * @return bool True on success, false otherwise
    */
    public function setPass($hash) {
      $ret = false;
      $sql = "UPDATE users SET password = ? WHERE login = ?";
      $stmt = self::$db->prepare($sql);
      $stmt->bind_param('ss', $hash, $this->login);
      $stmt->execute();
      if (!self::$db->error && !$stmt->errno) {
        $ret = true;
      }
      $stmt->close();
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
    * Store uUser object in session
    */
    public function storeInSession() {
      $_SESSION['user'] = $this;
    }

   /**
    * Fill uUser object properties from session data
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
    }

   /**
    * Get all users
    *
    * @return array|bool Array of uUser users, false on error
    */
    public function getAll() {
      $query = "SELECT id, login, password FROM users ORDER BY login";
      $result = self::$db->query($query);
      if ($result === false) {
        return false;
      }
      $userArr = [];
      while ($row = $result->fetch_assoc()) {
        $userArr[] = $this->rowToObject($row);
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
    private function rowToObject($row) {
      $user = new uUser();
      $user->id = $row['id'];
      $user->login = $row['login'];
      $user->hash = $row['password'];
      $user->isAdmin = $this->isAdmin($row['login']);
      $user->isValid = true;
      return $user;
    }

   /**
    * Is given login admin user
    *
    * @param string $login Login
    * @return bool True if admin, false otherwise
    */
    private function isAdmin($login) {
      $config = new uConfig();
      return (!empty($config::$admin_user) && $config::$admin_user == $login);
    }
  }
?>