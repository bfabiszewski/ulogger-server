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
  require_once(ROOT_DIR . "/helpers/position.php");

 /**
  * User handling routines
  */
  class uUser {
    public $id;
    public $login;
    public $hash;
    public $isAdmin = false;
    public $isValid = false;

   /**
    * Constructor
    *
    * @param string $login Login
    */
    public function __construct($login = NULL) {
      if (!empty($login)) {
        try {
          $query = "SELECT id, login, password, admin FROM " . self::db()->table('users') . " WHERE login = ? LIMIT 1";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $login ]);
          $stmt->bindColumn('id', $this->id, PDO::PARAM_INT);
          $stmt->bindColumn('login', $this->login);
          $stmt->bindColumn('password', $this->hash);
          $stmt->bindColumn('admin', $this->isAdmin, PDO::PARAM_BOOL);
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
     * Add new user
     *
     * @param string $login Login
     * @param string $pass Password
     * @param bool $isAdmin Is admin
     * @return int|bool New user id, false on error
     */
    public static function add($login, $pass, $isAdmin = false) {
      $userid = false;
      if (!empty($login) && !empty($pass)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $table = self::db()->table('users');
        try {
          $query = "INSERT INTO $table (login, password, admin) VALUES (?, ?, ?)";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $login, $hash, (int) $isAdmin ]);
          $userid = (int) self::db()->lastInsertId("${table}_id_seq");
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
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
        try {
          $query = "DELETE FROM " . self::db()->table('users') . " WHERE id = ?";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $this->id ]);
          $ret = true;
          $this->id = NULL;
          $this->login = NULL;
          $this->hash = NULL;
          $this->isValid = false;
          $this->isAdmin = false;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
      }
      return $ret;
    }

   /**
    * Set user admin status
    *
    * @param bool $isAdmin True if is admin
    * @return bool True on success, false otherwise
    */
    public function setAdmin($isAdmin) {
      $ret = false;
      try {
        $query = "UPDATE " . self::db()->table('users') . " SET admin = ? WHERE login = ?";
        $stmt = self::db()->prepare($query);
        $stmt->execute([ (int) $isAdmin, $this->login ]);
        $ret = true;
        $this->isAdmin = $isAdmin;
      } catch (PDOException $e) {
        // TODO: handle exception
        syslog(LOG_ERR, $e->getMessage());
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
      if (!empty($this->login) && !empty($pass)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        try {
          $query = "UPDATE " . self::db()->table('users') . " SET password = ? WHERE login = ?";
          $stmt = self::db()->prepare($query);
          $stmt->execute([ $hash, $this->login ]);
          $ret = true;
          $this->hash = $hash;
        } catch (PDOException $e) {
          // TODO: handle exception
          syslog(LOG_ERR, $e->getMessage());
        }
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
    * Store uUser object in session
    */
    public function storeInSession() {
      $_SESSION['user'] = $this;
    }

   /**
    * Fill uUser object properties from session data
    * @return uUser
    */
    public static function getFromSession() {
      $user = new uUser();
      if (isset($_SESSION['user'])) {
        $sessionUser = $_SESSION['user'];
        $user->id = $sessionUser->id;
        $user->login = $sessionUser->login;
        $user->hash = $sessionUser->hash;
        $user->isAdmin = $sessionUser->isAdmin;
        $user->isValid = $sessionUser->isValid;
      }
      return $user;
    }

   /**
    * Get all users
    *
    * @return uUser[]|bool Array of uUser users, false on error
    */
    public static function getAll() {
      try {
        $query = "SELECT id, login, password, admin FROM " . self::db()->table('users') . " ORDER BY login";
        $result = self::db()->query($query);
        $userArr = [];
        while ($row = $result->fetch()) {
          $userArr[] = self::rowToObject($row);
        }
      } catch (PDOException $e) {
        // TODO: handle exception
        syslog(LOG_ERR, $e->getMessage());
        $userArr = false;
      }
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
      $user->isAdmin = (bool) $row['admin'];
      $user->isValid = true;
      return $user;
    }
  }
?>
