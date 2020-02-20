<?php

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(__DIR__ . "/../../helpers/config.php");

abstract class BaseDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase {

  /**
   * @var PDO $pdo
   */
  static private $pdo;
  /**
   * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection $conn
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
    return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(TRUE);
  }

  public function setUp() {
    parent::setUp();
    $this->mockConfig = new uConfig(false);
  }

  public static function setUpBeforeClass() {
    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = Dotenv\Dotenv::create(__DIR__ . '/..');
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

  public static function tearDownAfterClass() {
    self::$pdo = null;
  }

  /**
   * Set up database connection
   * This will also override uDb class connection
   *
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  public function getConnection() {
    if ($this->conn === null) {
      $this->conn = $this->createDefaultDBConnection(self::$pdo, getenv('DB_NAME'));
    }
    return $this->conn;
  }

  /**
   * Create data set from xml fixture
   *
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  protected function getDataSet() {
    $this->resetAutoincrement();
    return $this->createFlatXMLDataSet(__DIR__ . '/../fixtures/fixture_empty.xml');
  }

  protected function resetAutoincrement($users = 1, $tracks = 1, $positions = 1, $layers = 1) {
    if (self::$driver === "pgsql") {
      self::$pdo->exec("ALTER SEQUENCE users_id_seq RESTART WITH $users");
      self::$pdo->exec("ALTER SEQUENCE tracks_id_seq RESTART WITH $tracks");
      self::$pdo->exec("ALTER SEQUENCE positions_id_seq RESTART WITH $positions");
      self::$pdo->exec("ALTER SEQUENCE ol_layers_id_seq RESTART WITH $layers");
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
   * Insert to database from array
   *
   * @param string $table Table name
   * @param array $rowsArr Array of rows
   * @return int|null Last insert id if available, NULL otherwise
   */
  private function pdoInsert($table, $rowsArr = []) {
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
  private function pdoInsertRaw($query) {
    $ret = NULL;
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
  protected function pdoGetColumn($query, $columnNumber = 0) {
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
   * @param string $user User login
   * @param string $pass User password
   * @param bool $isAdmin User is admin
   * @return int|bool User id or false on error
   */
  protected function addTestUser($user = NULL, $pass = NULL, $isAdmin = false) {
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
   * @param int $userId Optional track id
   * @param string $trackName Optional track name
   * @param string $comment Optional comment
   * @return int|bool Track id or false on error
   */
  protected function addTestTrack($userId = NULL, $trackName = NULL, $comment = NULL) {
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
   * @param int $userId
   * @param int $trackId
   * @param int $timeStamp
   * @param double $latitude
   * @param double $longitude
   * @return int|bool Position id or false on error
   */
  protected function addTestPosition($userId = NULL, $trackId = NULL, $timeStamp = NULL, $latitude = NULL, $longitude = NULL) {
    if (is_null($userId)) { $userId = $this->testUserId; }
    if (is_null($trackId)) { $trackId = $this->testTrackId; }
    if (is_null($timeStamp)) { $timeStamp = $this->testTimestamp; }
    if (is_null($latitude)) { $latitude = $this->testLat; }
    if (is_null($longitude)) { $longitude = $this->testLon; }

    $query = "INSERT INTO positions (user_id, track_id, time, latitude, longitude)
              VALUES ('$userId', '$trackId', " . $this->from_unixtime($timeStamp) . ", '$latitude', '$longitude')";
    return $this->pdoInsertRaw($query);
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
}
?>
