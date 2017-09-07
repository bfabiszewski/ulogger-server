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

  if (!defined('ROOT_DIR')) { define('ROOT_DIR', dirname(__DIR__)); }
  require_once(ROOT_DIR . "/helpers/user.php");
  require_once(ROOT_DIR . "/helpers/utils.php");
  if (!defined('BASE_URL')) { define('BASE_URL', uUtils::getBaseUrl()); }

 /**
  * Authentication
  */
  class uAuth {

    private $isAuthenticated = false;
    private $isLoginAttempt = false;
    public $user = null;

    public function __construct() {
      $this->sessionStart();

      $user = (new uUser())->getFromSession();
      if ($user->isValid) {
        $this->setAuthenticated($user);
      } else {
        $this->checkLogin();
      }
    }

    /**
     * Is user authenticated
     *
     * @return boolean True if authenticated, false otherwise
     */
    public function isAuthenticated() {
      return $this->isAuthenticated;
    }

    /**
     * Has user attempted to log in
     *
     * @return boolean True if attempted login, false otherwise
     */
    public function isLoginAttempt() {
      return $this->isLoginAttempt;
    }

    /**
     * Is authenticated user admin
     *
     * @return boolean True if admin, false otherwise
     */
    public function isAdmin() {
      return ($this->isAuthenticated && $this->user->isAdmin);
    }

    /**
     * Start php session
     *
     * @return void
     */
    private function sessionStart() {
      session_name("ulogger");
      session_start();
    }

    /**
     * Terminate php session
     *
     * @return void
     */
    private function sessionEnd() {
      $_SESSION = [];
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name('ulogger'), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
        );
      }
      session_destroy();
    }

    /**
     * Clean session variables
     *
     * @return void
     */
    private function sessionCleanup() {
      $_SESSION = [];
    }

    /**
     * Mark as authenticated, set user
     *
     * @param [type] $user
     * @return void
     */
    private function setAuthenticated($user) {
      $this->isAuthenticated = true;
      $this->user = $user;
    }

    /**
     * Process log in request
     *
     * @return void
     */
    private function checkLogin() {
      $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : NULL;
      $login = isset($_REQUEST["user"]) ? $_REQUEST["user"] : NULL;
      $pass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : NULL;

      if ($action == "auth" && !is_null($login) && !is_null($pass)) {
        $this->isLoginAttempt = true;
        if (!empty($login) && !empty($pass)) {
          $user = new uUser($login);
          if ($user->isValid && $user->validPassword($pass)) {
            $this->setAuthenticated($user);
            $this->sessionCleanup();
            $user->storeInSession();
          }
        }
      }
    }

    /**
     * Log out with redirect
     *
     * @param string $path URL path
     * @return void
     */
    public function logOutWithRedirect($path = "") {
      $this->sessionEnd();
      $this->exitWithRedirect($path);
    }

    /**
     * Send 401 headers
     *
     * @return void
     */
    public function sendUnauthorizedHeader() {
      header('WWW-Authenticate: OAuth realm="users@ulogger"');
      header('HTTP/1.1 401 Unauthorized', true, 401);
    }

    /**
     * Send 401 headers and exit
     *
     * @return void
     */
    public function exitWithUnauthorized() {
      $this->sendUnauthorizedHeader();
      exit();
    }

    /**
     * Redirect browser and exit
     *
     * @param string $path Redirect URL path
     * @return void
     */
    public function exitWithRedirect($path = "") {
      header("Location: " . BASE_URL . $path);
      exit();
    }
  }