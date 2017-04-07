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

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/db.php");

class uUser {
    public $id;
    public $login;
    public $hash;
    public $isAdmin = false;
    public $isValid = false;

    private static $db;

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

    public function validPassword($password) {
      return password_verify($password, $this->hash);
    }

    public function storeInSession() {
      $_SESSION['user'] = $this;
    }

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

    private function rowToObject($row) {
      $user = new uUser();
      $user->id = $row['id'];
      $user->login = $row['login'];
      $user->hash = $row['password'];
      $user->isAdmin = $this->isAdmin($row['login']);
      $user->isValid = true;
      return $user;
    }

    private function isAdmin($login) {
      $config = new uConfig();
      return (!empty($config::$admin_user) && $config::$admin_user == $login);
    }
}

 ?>