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
     * Table names
     *
     * @var array Array of names
     */
    protected static $tables;

   /**
    * Private constuctor
    *
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param string $name
    * @param int $port
    * @param string $socket
    */
    public function __construct($host, $user, $pass, $name, $port = null, $socket = null) {
      @parent::__construct($host, $user, $pass, $name, $port, $socket);
      if ($this->connect_error) {
        header("HTTP/1.1 503 Service Unavailable");
        die("Database connection error (" . $this->connect_error . ")");
      }
      $this->set_charset('utf8');
      $this->initTables();
    }

    /**
     * Initialize table names based on config
     */
    private function initTables() {
      self::$tables = [];
      $prefix = preg_replace('/[^a-z0-9_]/i', '', uConfig::$dbprefix);
      self::$tables['positions'] = $prefix . "positions";
      self::$tables['tracks'] = $prefix . "tracks";
      self::$tables['users'] = $prefix . "users";
    }

   /**
    * Returns singleton instance
    *
    * @return object Singleton instance
    */
    public static function getInstance() {
      if (!self::$instance) {
        self::$instance = new self(uConfig::$dbhost, uConfig::$dbuser, uConfig::$dbpass, uConfig::$dbname);
      }
      return self::$instance;
    }

   /**
    * Get full table name including prefix
    *
    * @param string $name Name
    * @return string Full table name
    */
    public function table($name) {
      return self::$tables[$name];
    }
  }
?>
