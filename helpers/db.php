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

require_once(ROOT_DIR . "/helpers/utils.php");

/**
 * PDO wrapper
 */
class uDb extends PDO {
  /**
   * Singleton instance
   *
   * @var uDb Object instance
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
   * @var string Driver
   */
  protected static $driver;

  /**
   * @var string Database DSN
   */
  private static $dbdsn = "";
  /**
   * @var string Database user
   */
  private static $dbuser = "";
  /**
   * @var string Database pass
   */
  private static $dbpass = "";
  /**
   * @var string Optional table names prefix, eg. "ulogger_"
   */
  private static $dbprefix = "";

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
    $prefix = preg_replace('/[^a-z0-9_]/i', '', self::$dbprefix);
    self::$tables['positions'] = $prefix . "positions";
    self::$tables['tracks'] = $prefix . "tracks";
    self::$tables['users'] = $prefix . "users";
    self::$tables['config'] = $prefix . "config";
    self::$tables['ol_layers'] = $prefix . "ol_layers";
  }

  /**
   * Returns singleton instance
   *
   * @return uDb Singleton instance
   */
  public static function getInstance() {
    if (!self::$instance) {
      self::getConfig();
      self::$instance = new self(self::$dbdsn, self::$dbuser, self::$dbpass);
    }
    return self::$instance;
  }

  /**
   * Read database setup from config file
   * @noinspection IssetArgumentExistenceInspection
   * @noinspection PhpIncludeInspection
   */
  private static function getConfig() {
    $configFile = dirname(__DIR__) . "/config.php";
    if (!file_exists($configFile)) {
      header("HTTP/1.1 503 Service Unavailable");
      die("Missing config.php file!");
    }
    include($configFile);
    if (isset($dbdsn)) {
      self::$dbdsn = self::normalizeDsn($dbdsn);
    }
    if (isset($dbuser)) {
      self::$dbuser = $dbuser;
    }
    if (isset($dbpass)) {
      self::$dbpass = $dbpass;
    }
    if (isset($dbprefix)) {
      self::$dbprefix = $dbprefix;
    }
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

  /**
   * Returns function name for getting date-time column value as unix timestamp
   * @param string $column
   * @return string
   */
  public function unix_timestamp($column) {
    switch (self::$driver) {
      default:
      case "mysql":
        return "UNIX_TIMESTAMP($column)";
        break;
      case "pgsql":
        return "EXTRACT(EPOCH FROM $column::TIMESTAMP WITH TIME ZONE)";
        break;
      case "sqlite":
        return "STRFTIME('%s', $column)";
        break;
    }
  }

  /**
   * Returns placeholder for LOB data types
   * @return string
   */
  public function lobPlaceholder() {
    switch (self::$driver) {
      default:
      case "mysql":
      case "sqlite":
      return "?";
        break;
      case "pgsql":
        return "?::bytea";
        break;
    }
  }

  /**
   * Returns construct for getting LOB as string
   * @param string $column Column name
   * @return string
   */
  public function from_lob($column) {
    switch (self::$driver) {
      default:
      case "mysql":
      case "sqlite":
        return $column;
        break;
      case "pgsql":
        return "encode($column, 'escape') AS $column";
        break;
    }
  }

  /**
   * Returns function name for getting date-time column value as 'YYYY-MM-DD hh:mm:ss'
   * @param string $column
   * @return string
   */
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

  /**
   * Replace into
   * Note: requires PostgreSQL >= 9.5
   * @param string $table Table name (without prefix)
   * @param string[] $columns Column names
   * @param string[][] $values Values [ [ value1, value2 ], ... ]
   * @param string $key Unique column
   * @param string $update Updated column
   * @return string
   */
  public function insertOrReplace($table, $columns, $values, $key, $update) {
    $cols = implode(", ", $columns);
    $rows = [];
    foreach ($values as $row) {
      $rows[] = "(" . implode(", ", $row) . ")";
    }
    $vals = implode(", ", $rows);
    switch (self::$driver) {
      default:
      case "mysql":
        return "INSERT INTO {$this->table($table)} ($cols)
          VALUES $vals
          ON DUPLICATE KEY UPDATE $update = VALUES($update)";
        break;
      case "pgsql":
        return "INSERT INTO {$this->table($table)} ($cols)
          VALUES $vals
          ON CONFLICT ($key) DO UPDATE SET $update = EXCLUDED.$update";
        break;
      case "sqlite":
        return "REPLACE INTO {$this->table($table)} ($cols)
          VALUES $vals";
        break;
    }
  }

  /**
   * Set character set
   * @param string $charset
   */
  private function setCharset($charset) {
    if (self::$driver === "pgsql" || self::$driver === "mysql") {
      $this->exec("SET NAMES '$charset'");
    }
  }

  /**
   * Extract database name from DSN
   * @param string $dsn
   * @return string Empty string if not found
   */
  public static function getDbName($dsn) {
    $name = "";
    if ($dsn && strpos($dsn, ":") !== false) {
      list($scheme, $dsnWithoutScheme) = explode(":", $dsn, 2);
      switch ($scheme) {
        case "sqlite":
        case "sqlite2":
        case "sqlite3":
          $pattern = "/(.+)/";
          break;
        case "pgsql":
          $pattern = "/dbname=([^; ]+)/";
          break;
        default:
          $pattern = "/dbname=([^;]+)/";
          break;
      }
      $result = preg_match($pattern, $dsnWithoutScheme, $matches);
      if ($result === 1) {
        $name = $matches[1];
      }
    }
    return $name;
  }

  /**
   * Normalize DSN.
   * Make sure sqlite DSN file path is absolute
   * @param string $dsn DSN
   * @return string Normalized DSN
   */
  public static function normalizeDsn($dsn) {
    if (stripos($dsn, "sqlite") !== 0) {
      return $dsn;
    }
    $arr = explode(":", $dsn, 2);
    if (count($arr) < 2 || empty($arr[1]) || uUtils::isAbsolutePath($arr[1])) {
      return $dsn;
    }
    $scheme = $arr[0];
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $arr[1];
    return $scheme . ":" . realpath(dirname($path)) . DIRECTORY_SEPARATOR . basename(($path));
  }
}
?>
