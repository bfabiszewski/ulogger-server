<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper;

use PDO;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use uLogger\Component\Db;
use uLogger\Exception\DatabaseException;
use uLogger\Mapper\MapperFactory;

abstract class AbstractMapperTestCase extends TestCase {

  use DatabaseTestTrait;

  private PDO $pdo;
  protected Db $db;
  protected MapperFactory $mapperFactory;

  protected const TEST_USER = 'testUser';
  protected const TEST_USER2 = 'testUser2';
  protected const TEST_PASS = 'testPass1234567890-;';
  protected const TEST_ADMIN_ID = 1;
  protected const TEST_USER2_ID = 2;
  protected const TEST_ADMIN_USER = 'admin';
  protected const TEST_ADMIN_PASS = 'admin';
  protected const TEST_TRACK_ID = 1;
  protected const TEST_TRACK2_ID = 2;
  protected const TEST_TRACK_NAME = 'test track';
  protected const TEST_TRACK_COMMENT = 'test track comment';
  protected const TEST_TIMESTAMP = 1502974402;
  protected const TEST_LATITUDE = 0.0;
  protected const TEST_LONGITUDE = 10.604001083;
  protected const TEST_ALTITUDE = 10.01;
  protected const TEST_SPEED = 10.01;
  protected const TEST_BEARING = 10.01;
  protected const TEST_ACCURACY = 10;
  protected const TEST_PROVIDER = 'gps';
  protected const TEST_COMMENT = 'test comment';
  protected const TEST_IMAGE = '1234_1502974402_5d1a1960335cf.jpg';
  private ?string $driver;

  /**
   * @throws DatabaseException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpConnection($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
    $this->setUpDatabase(__DIR__ . '/Schemas/mysql.sql');
  }

  public function getConnection(): PDO {
    return $this->pdo;
  }

  /**
   * @param class-string $className
   * @param string $key
   * @param mixed $value
   * @return array|null
   */
  public function getRecordByKey(string $className, string $key, mixed $value): array|null {
    $array = (new $className())->records;
    return $this->getArrayRowByKey($array, $key, $value);
  }

  /**
   * @param class-string $className
   * @param int $id
   * @return array|null
   */
  public function getRecordById(string $className, int $id): array|null {
    return $this->getRecordByKey($className, 'id', $id);
  }

  /**
   * @param string $dsn
   * @param string $user
   * @param string $password
   * @return void
   * @throws DatabaseException
   */
  private function setUpConnection(string $dsn, string $user, string $password): void {
    $this->pdo = new PDO($dsn, $user, $password);
    $this->db = new Db($dsn, $user, $password);
    $this->mapperFactory = new MapperFactory($this->db);
    $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
  }

  /**
   * Fetch row by ID.
   *
   * @param string $table Table name
   * @return array Row
   */
  protected function getTableAllRows(string $table): array {
    $sql = sprintf('SELECT * FROM `%s`', $table);
    $statement = $this->createPreparedStatement($sql);
    $statement->execute();

    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * @param mixed $array
   * @param string $key
   * @param mixed $value
   * @return mixed|null
   */
  public function getArrayRowByKey(array $array, string $key, mixed $value): array|null {
    foreach ($array as $record) {
      if ($record[$key] === $value) {
        return $record;
      }
    }
    return null;
  }



  /**
   * Insert to database from array
   *
   * @param string $table Table name
   * @param array $rowsArr Array of rows
   * @return int|null Last insert id if available, null otherwise
   */
  private function pdoInsert(string $table, array $rowsArr = []): ?int {
    $ret = null;
    if (!empty($rowsArr)) {
      $values = ':' . implode(', :', array_keys($rowsArr));
      $columns = implode(', ', array_keys($rowsArr));
      $query = "INSERT INTO $table ($columns) VALUES ($values)";
      $stmt = $this->pdo->prepare($query);
      if ($stmt !== false) {
        $stmt->execute(array_combine(explode(', ', $values), array_values($rowsArr)));
      }
      $ret = (int) $this->pdo->lastInsertId();
    }
    return $ret;
  }

  /**
   * Execute raw insert query on database
   *
   * @param string $query Insert query
   * @return int|null Last insert id if available, null otherwise
   */
  private function pdoInsertRaw(string $query): ?int {
    $ret = null;
    if ($this->pdo->exec($query) !== false) {
      $ret = (int) $this->pdo->lastInsertId();
    }
    return $ret;
  }

  /**
   * Get single column from first row of query result
   *
   * @param string $query SQL query
   * @param int $columnNumber Optional column number (default is first column)
   * @return string|bool Column  or false if no data
   */
  protected function pdoGetColumn(string $query, int $columnNumber = 0): bool|string {
    $column = false;
    $stmt = $this->pdo->query($query);
    if ($stmt !== false) {
      $column = $stmt->fetchColumn($columnNumber);
      $stmt->closeCursor();
    }
    return $column;
  }

  /**
   * Insert user data to database
   * If parameters are omitted they default test values are used
   *
   * @param string|null $user User login
   * @param string|null $pass User password
   * @param bool $isAdmin User is admin
   * @return int|bool User id or false on error
   */
  protected function addTestUser(?string $user = null, ?string $pass = null, bool $isAdmin = false): bool|int {
    if (is_null($user)) {
      $user = self::TEST_USER;
    }
    if (is_null($pass)) {
      $pass = self::TEST_PASS;
    }
    $id = $this->pdoInsert('users', [ 'login' => $user, 'password' => $pass, 'admin' => (int) $isAdmin ]);
    if ($id !== false) {
      return (int) $id;
    }
    return false;
  }

  /**
   * Insert track data to database.
   * If parameters are omitted they default test values are used
   *
   * @param int|null $userId Optional track id
   * @param string|null $trackName Optional track name
   * @param string|null $comment Optional comment
   * @return int|bool Track id or false on error
   */
  protected function addTestTrack(?int $userId = null, ?string $trackName = null, ?string $comment = null): bool|int {
    if (is_null($userId)) {
      $userId = self::TEST_ADMIN_ID;
    }
    if (is_null($trackName)) {
      $trackName = self::TEST_TRACK_NAME;
    }
    if (is_null($comment)) {
      $comment = self::TEST_TRACK_COMMENT;
    }
    $id = $this->pdoInsert('tracks', [ 'user_id' => $userId, 'name' => $trackName, 'comment' => $comment ]);
    if ($id !== false) {
      return (int) $id;
    }
    return false;
  }

  /**
   * Insert config value to database
   *
   * @param string $name Config option name
   * @param string|null $value Config option value
   * @return void
   */
  protected function addTestConfigValue(string $name, ?string $value): void {
    $this->pdoInsert('config', [ 'name' => $name, 'value' => $value ]);
  }

  /**
   * Insert position data to database
   * If parameters are omitted they default test values are used
   *
   * @param int|null $userId
   * @param int|null $trackId
   * @param int|null $timeStamp
   * @param float|null $latitude
   * @param float|null $longitude
   * @return int|null Position id or false on error
   */
  protected function addTestPosition(?int $userId = null, ?int $trackId = null, ?int $timeStamp = null, ?float $latitude = null, ?float $longitude = null): ?int {
    if (is_null($userId)) {
      $userId = self::TEST_ADMIN_ID;
    }
    if (is_null($trackId)) {
      $trackId = self::TEST_TRACK_ID;
    }
    if (is_null($timeStamp)) {
      $timeStamp = self::TEST_TIMESTAMP;
    }
    if (is_null($latitude)) {
      $latitude = self::TEST_LATITUDE;
    }
    if (is_null($longitude)) {
      $longitude = self::TEST_LONGITUDE;
    }

    $query = "INSERT INTO positions (user_id, track_id, time, latitude, longitude)
              VALUES ('$userId', '$trackId', " . $this->fromUnixtime($timeStamp) . ", '$latitude', '$longitude')";
    return $this->pdoInsertRaw($query);
  }

  /**
   * Get function that converts date time column to unix timestamp for current PDO driver
   * @param string $column Column name or timestamp value
   * @return string
   */
  public function unixTimestamp(string $column): string {
    switch ($this->driver) {
      default:
      case 'mysql':
        return "UNIX_TIMESTAMP($column)";
      case 'pgsql':
        return "EXTRACT(EPOCH FROM $column)";
      case 'sqlite':
        return "STRFTIME('%s', $column)";
    }
  }

  /**
   * Get function that converts unix timestamp to date time for current PDO driver
   * @param int|string $column Column name or timestamp value
   * @return string
   */
  public function fromUnixtime(int|string $column): string {
    switch ($this->driver) {
      default:
      case 'mysql':
        return "FROM_UNIXTIME($column)";
      case 'pgsql':
        return "TO_TIMESTAMP($column)";
      case 'sqlite':
        return "DATETIME($column, 'unixepoch')";
    }
  }

}
