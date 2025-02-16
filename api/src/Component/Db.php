<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Component;

use PDO;
use PDOException;
use uLogger\Exception\DatabaseException;
use uLogger\Helper\Utils;

/**
 * PDO wrapper
 */
class Db extends PDO {

  /**
   * Table names
   *
   * @var array Array of names
   */
  protected static array $tables;

  /**
   * Database driver name
   *
   * @var string Driver
   */
  protected static string $driver;

  /**
   * @var string Database DSN
   */
  private static string $dsn = '';
  /**
   * @var string Database user
   */
  private static string $user = '';
  /**
   * @var string Database pass
   */
  private static string $password = '';
  /**
   * @var string Optional table names prefix, eg. "ulogger_"
   */
  private static string $prefix = '';

  /**
   * PDO constructor
   *
   * @param string $dsn
   * @param string $user
   * @param string $pass
   * @throws DatabaseException
   */
  public function __construct(string $dsn, string $user, string $pass) {
    try {
      $options = [
        PDO::ATTR_EMULATE_PREPARES   => false, // try to use native prepared statements
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // return assoc array by default
      ];
      @parent::__construct($dsn, $user, $pass, $options);
      self::$driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
      $this->setCharset('utf8');
      $this->initTables();
    } catch (PDOException $e) {
      throw new DatabaseException($e->getMessage());
    }
  }

  /**
   * Initialize table names based on config
   */
  private function initTables(): void {
    self::$tables = [];
    $prefix = preg_replace('/[^a-z0-9_]/i', '', self::$prefix);
    self::$tables['positions'] = $prefix . 'positions';
    self::$tables['tracks'] = $prefix . 'tracks';
    self::$tables['users'] = $prefix . 'users';
    self::$tables['config'] = $prefix . 'config';
    self::$tables['ol_layers'] = $prefix . 'ol_layers';
  }

  /**
   * @throws DatabaseException
   */
  public static function createFromConfig(): Db {
    self::getConfig();
    return new self(self::$dsn, self::$user, self::$password);
  }

  /**
   * Read database setup from config file
   * @noinspection IssetArgumentExistenceInspection
   * @throws DatabaseException
   */
  private static function getConfig(): void {
    $configFile = dirname(__DIR__, 2) . '/config/config.php';
    if (!file_exists($configFile)) {
      throw new DatabaseException('Missing config.php file');
    }
    include($configFile);
    if (isset($dbdsn)) {
      self::$dsn = self::normalizeDsn($dbdsn);
    }
    if (isset($dbuser)) {
      self::$user = $dbuser;
    }
    if (isset($dbpass)) {
      self::$password = $dbpass;
    }
    if (isset($dbprefix)) {
      self::$prefix = $dbprefix;
    }
  }

  /**
   * Get full table name including prefix
   *
   * @param string $name Name
   * @return string Full table name
   */
  public function table(string $name): string {
    return self::$tables[$name];
  }

  /**
   * Returns function name for getting date-time column value as unix timestamp
   * @param string $column
   * @return string
   */
  public function unixTimestamp(string $column): string {
    switch (self::$driver) {
      default:
      case 'mysql':
        return "UNIX_TIMESTAMP($column)";
      case 'pgsql':
        return "EXTRACT(EPOCH FROM $column::TIMESTAMP WITH TIME ZONE)::INT";
      case 'sqlite':
        return "UNIXEPOCH($column)";
    }
  }

  /**
   * Returns placeholder for LOB data types
   * @return string
   */
  public function lobPlaceholder(): string {
    switch (self::$driver) {
      default:
      case 'mysql':
      case 'sqlite':
        return '?';
      case 'pgsql':
        return '?::bytea';
    }
  }

  /**
   * Returns construct for getting LOB as string
   * @param string $column Column name
   * @return string
   */
  public function fromLob(string $column): string {
    switch (self::$driver) {
      default:
      case 'mysql':
      case 'sqlite':
        return $column;
      case 'pgsql':
        return "encode($column, 'escape') AS $column";
    }
  }

  /**
   * Returns function name for getting date-time column value as 'YYYY-MM-DD hh:mm:ss'
   * @param string $column
   * @return string
   */
  public function fromUnixTime(string $column): string {
    switch (self::$driver) {
      default:
      case 'mysql':
        return "FROM_UNIXTIME($column)";
      case 'pgsql':
        return "TO_TIMESTAMP($column)";
      case 'sqlite':
        return "DATETIME($column, 'unixepoch')";
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
  public function insertOrReplace(string $table, array $columns, array $values, string $key, string $update): string {
    $cols = implode(', ', $columns);
    $rows = [];
    foreach ($values as $row) {
      $rows[] = '(' . implode(', ', $row) . ')';
    }
    $vals = implode(', ', $rows);
    switch (self::$driver) {
      default:
      case 'mysql':
        return "INSERT INTO {$this->table($table)} ($cols)
                VALUES $vals
                ON DUPLICATE KEY UPDATE $update = VALUES($update)";
      case 'pgsql':
        return "INSERT INTO {$this->table($table)} ($cols)
                VALUES $vals
                ON CONFLICT ($key) DO UPDATE SET $update = EXCLUDED.$update";
      case 'sqlite':
        return "REPLACE INTO {$this->table($table)} ($cols)
                VALUES $vals";
    }
  }

  /**
   * Set character set
   * @param string $charset
   * @noinspection PhpSameParameterValueInspection
   */
  private function setCharset(string $charset): void {
    if (self::$driver === 'pgsql' || self::$driver === 'mysql') {
      $this->exec("SET NAMES '$charset'");
    }
  }

  /**
   * Extract database name from DSN
   * @param string $dsn
   * @return string Empty string if not found
   */
  public static function getDbName(string $dsn): string {
    $name = '';
    if (str_contains($dsn, ':')) {
      [$scheme, $dsnWithoutScheme] = explode(':', $dsn, 2);
      $pattern = match ($scheme) {
        'sqlite', 'sqlite2', 'sqlite3' => '/(.+)/',
        'pgsql' => '/dbname=([^; ]+)/',
        default => '/dbname=([^;]+)/',
      };
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
  public static function normalizeDsn(string $dsn): string {
    if (stripos($dsn, 'sqlite') !== 0) {
      return $dsn;
    }
    $arr = explode(':', $dsn, 2);
    if (count($arr) < 2 || empty($arr[1]) || Utils::isAbsolutePath($arr[1])) {
      return $dsn;
    }
    $scheme = $arr[0];
    $path = Utils::getRootDir() . DIRECTORY_SEPARATOR . $arr[1];
    return $scheme . ':' . realpath(dirname($path)) . DIRECTORY_SEPARATOR . basename(($path));
  }
}
?>
