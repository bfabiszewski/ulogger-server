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

 /**
  * mysqli wrapper
  */
  class uDb extends mysqli {
    /**
     * Singleton instance
     *
     * @var mysqli Object instance
     */
    protected static $instance;

   /**
    * Private constuctor
    *
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param string $name
    */
    private function __construct($host, $user, $pass, $name) {
      parent::__construct($host, $user, $pass, $name);
      if ($this->connect_error) {
        if (defined('headless')) {
          header("HTTP/1.1 503 Service Unavailable");
          exit;
        }
        die("Database connection error (" . $this->connect_errno . ")");
      }
      $this->set_charset('utf8');
    }

   /**
    * Returns singleton instance
    */
    public static function getInstance() {
      if (!self::$instance) {
        $config = new uConfig();
        self::$instance = new self($config::$dbhost, $config::$dbuser, $config::$dbpass, $config::$dbname);
      }
      return self::$instance;
    }
  }
?>