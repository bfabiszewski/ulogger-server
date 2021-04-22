<?php

use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\IDataSet;

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(__DIR__ . "/../../helpers/config.php");

abstract class BaseDatabaseTestCase extends PHPUnit\DbUnit\TestCase {

  /**
   * @var PDO $pdo
   */
  static private $pdo;
  /**
   * @var PHPUnit\DbUnit\Database\Connection $conn
   */
  private $conn;
  static private $driver = "mysql";

  protected $mockConfig;

  protected $testUser = "testUser";
  protected $testUser2 = "testUser2";
  protected $testAdminUser = "admin";
  protected $testAdminPass = "admin";
  protected $testPass = "testPass1234567890-;";
  protected $testUserId = 1;
  protected $testUserId2 = 2;
  protected $testUserId3 = 3;
  protected $testTrackId = 1;
  protected $testTrackId2 = 2;
  protected $testTrackName = "test track";
  protected $testTrackComment = "test track comment";
  protected $testTimestamp = 1502974402;
  protected $testLat = 0.0;
  protected $testLon = 10.604001083;
  protected $testAltitude = 10.01;
  protected $testSpeed = 10.01;
  protected $testBearing = 10.01;
  protected $testAccuracy = 10;
  protected $testProvider = "gps";
  protected $testComment = "test comment";
  protected $testImage = "1234_1502974402_5d1a1960335cf.jpg";

  // Fixes PostgreSQL: "cannot truncate a table referenced in a foreign key constraint"
  protected function getSetUpOperation() {
    return PHPUnit\DbUnit\Operation\Factory::CLEAN_INSERT(true);
  }

  public function setUp(): void {
    parent::setUp();
    $this->mockConfig = new uConfig(false);
  }

  public static function setUpBeforeClass(): void {
    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
      $dotenv->load();
      $dotenv->required(['DB_DSN', 'DB_USER', 'DB_PASS']);
    }

    $db_dsn = getenv('DB_DSN');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    // pdo connection
    if (self::$pdo == null) {
      self::$pdo = new PDO($db_dsn, $db_user, $db_pass);
      self::$driver = self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
  }

  public static function tearDownAfterClass(): void {
    self::$pdo = null;
  }

  /**
   * Set up database connection
   * This will also override uDb class connection
   *
   * @return Connection
   */
  public function getConnection(): Connection {
    if ($this->conn === null) {
      $this->conn = $this->createDefaultDBConnection(self::$pdo, getenv('DB_NAME'));
    }
    return $this->conn;
  }

  /**
   * Create data set from xml fixture
   *
   * @return PHPUnit\DbUnit\DataSet\IDataSet
   */
  protected function getDataSet(): IDataSet {
    $this->resetAutoincrement();
    return $this->createFlatXMLDataSet(__DIR__ . '/../fixtures/fixture_empty.xml');
  }

  protected function resetAutoincrement($users = 1, $tracks = 1, $positions = 1, $layers = 1): void {
    if (self::$driver === "pgsql") {
      self::$pdo->exec("ALTER SEQUENCE IF EXISTS users_id_seq RESTART WITH $users");
      self::$pdo->exec("ALTER SEQUENCE IF EXISTS tracks_id_seq RESTART WITH $tracks");
      self::$pdo->exec("ALTER SEQUENCE IF EXISTS positions_id_seq RESTART WITH $positions");
      self::$pdo->exec("ALTER SEQUENCE IF EXISTS ol_layers_id_seq RESTART WITH $layers");
    } else if (self::$driver === "sqlite") {
      $retry = 1;
      do {
        try {
          self::$pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'users'");
          self::$pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'tracks'");
          self::$pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'positions'");
          self::$pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'ol_layers'");
          $retry = 0;
        } catch (Exception $e) {
          // sqlite raises error when db schema changes in another connection.
          if (strpos($e->getMessage(), 'database schema has changed') !== false) {
            self::$pdo = null;
            self::setUpBeforeClass();
          }
        }
      } while ($retry--);
    }
  }

  /**
   * Reset connection
   * Fixes sqlite error when db schema changes in another connection.
   */
  protected function resetConnection(): void {
    $this->closeConnection($this->conn);
    $this->conn = null;
    self::tearDownAfterClass();
    self::setUpBeforeClass();
  }

  /**
   * Insert to database from array
   *
   * @param string $table Table name
   * @param array $rowsArr Array of rows
   * @return int|null Last insert id if available, NULL otherwise
   */
  private function pdoInsert(string $table, array $rowsArr = []): ?int {
    $ret = NULL;
    if (!empty($rowsArr)) {
      $values = ':' . implode(', :', array_keys($rowsArr));
      $columns = implode(', ', array_keys($rowsArr));
      $query = "INSERT INTO $table ($columns) VALUES ($values)";
      $stmt = self::$pdo->prepare($query);
      if ($stmt !== false) {
        $stmt->execute(array_combine(explode(', ', $values), array_values($rowsArr)));
      }
      $ret = self::$pdo->lastInsertId();
    }
    return $ret;
  }

  /**
   * Execute raw insert query on database
   *
   * @param string $query Insert query
   * @return int|null Last insert id if available, NULL otherwise
   */
  private function pdoInsertRaw(string $query): ?int {
    $ret = null;
    if (self::$pdo->exec($query) !== false) {
      $ret = self::$pdo->lastInsertId();
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
  protected function pdoGetColumn(string $query, int $columnNumber = 0) {
    $column = false;
    $stmt = self::$pdo->query($query);
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
  protected function addTestUser(?string $user = null, ?string $pass = null, bool $isAdmin = false) {
    if (is_null($user)) { $user = $this->testUser; }
    if (is_null($pass)) { $pass = $this->testPass; }
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
  protected function addTestTrack(?int $userId = null, ?string $trackName = null, ?string $comment = null) {
    if (is_null($userId)) { $userId = $this->testUserId; }
    if (is_null($trackName)) { $trackName = $this->testTrackName; }
    if (is_null($comment)) { $comment = $this->testTrackComment; }
    $id = $this->pdoInsert('tracks', [ 'user_id' => $userId, 'name' => $trackName, 'comment' => $comment ]);
    if ($id !== false) {
      return (int) $id;
    }
    return false;
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
    if (is_null($userId)) { $userId = $this->testUserId; }
    if (is_null($trackId)) { $trackId = $this->testTrackId; }
    if (is_null($timeStamp)) { $timeStamp = $this->testTimestamp; }
    if (is_null($latitude)) { $latitude = $this->testLat; }
    if (is_null($longitude)) { $longitude = $this->testLon; }

    $query = "INSERT INTO positions (user_id, track_id, time, latitude, longitude)
              VALUES ('$userId', '$trackId', " . $this->from_unixtime($timeStamp) . ", '$latitude', '$longitude')";
    return $this->pdoInsertRaw($query);
  }

  public function unix_timestamp(string $column): string {
    switch (self::$driver) {
      default:
      case "mysql":
        return "UNIX_TIMESTAMP($column)";
      case "pgsql":
        return "EXTRACT(EPOCH FROM $column)";
      case "sqlite":
        return "STRFTIME('%s', $column)";
    }
  }

  public function from_unixtime(string $column): string {
    switch (self::$driver) {
      default:
      case "mysql":
        return "FROM_UNIXTIME($column)";
      case "pgsql":
        return "TO_TIMESTAMP($column)";
      case "sqlite":
        return "DATETIME($column, 'unixepoch')";
    }
  }
}
?>
