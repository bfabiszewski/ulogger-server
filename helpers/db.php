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
  * PDO wrapper
  */
  class uDb extends PDO {
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
     * Database driver name
     *
     * @var String Driver
     */
    protected static $driver;

   /**
    * PDO constuctor
    *
    * @param string $dsn
    * @param string $user
    * @param string $pass
    */
    public function __construct($dsn, $user, $pass) {
      try {
        $options = [
          PDO::ATTR_EMULATE_PREPARES   => false, // try to use native prepared statements
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // return assoc array by default
        ];
        @parent::__construct($dsn, $user, $pass, $options);
        self::$driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->setCharset("utf8");
        $this->initTables();
      } catch (PDOException $e) {
        header("HTTP/1.1 503 Service Unavailable");
        die("Database connection error (" . $e->getMessage() . ")");
      }
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
        self::$instance = new self(uConfig::$dbdsn, uConfig::$dbuser, uConfig::$dbpass);
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

    public function unix_timestamp($column) {
      switch (self::$driver) {
        default:
        case "mysql":
          return "UNIX_TIMESTAMP($column)";
          break;
        case "pgsql":
          return "EXTRACT(EPOCH FROM $column)";
          break;
        case "sqlite":
          return "STRFTIME('%s', $column)";
          break;
      }
    }

    public function from_unixtime($column) {
      switch (self::$driver) {
        default:
        case "mysql":
          return "FROM_UNIXTIME($column)";
          break;
        case "pgsql":
          return "TO_TIMESTAMP($column)";
          break;
        case "sqlite":
          return "DATETIME($column, 'unixepoch')";
          break;
      }
    }

    private function setCharset($charset) {
      if (self::$driver == "pgsql" || self::$driver == "mysql") {
        $this->query("SET NAMES '$charset'");
      }
    }
  }
?>
