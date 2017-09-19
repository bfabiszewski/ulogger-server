<?php
use PHPUnit\Framework\TestCase;

abstract class BaseDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase {

  static private $pdo = null;
  private $conn = null;

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
  protected $testLat = 0;
  protected $testLon = 10.604001083;
  protected $testAltitude = 10.01;
  protected $testSpeed = 10.01;
  protected $testBearing = 10.01;
  protected $testAccuracy = 10;
  protected $testProvider = "gps";
  protected $testComment = "test comment";
  protected $testImageId = 1;

  public static function setUpBeforeClass() {
    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
      $dotenv->load();
      $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
    }

    $db_host = getenv('DB_HOST');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');
    $db_port = getenv('DB_PORT') ?: NULL;
    $db_dsn = "mysql:dbname={$db_name};host={$db_host}";
    if (!empty($db_port)) {
      $db_dsn .= ";port={$db_port}";
    }

    // pdo connection
    if (self::$pdo == null) {
      self::$pdo = new PDO($db_dsn, $db_user, $db_pass);;
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
    return $this->createMySQLXMLDataSet(__DIR__ . '/../fixtures/fixture_empty.xml');
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
   * @return int|bool User id or false on error
   */
  protected function addTestUser($user = NULL, $pass = NULL) {
    if (is_null($user)) { $user = $this->testUser; }
    if (is_null($pass)) { $pass = $this->testPass; }
    return $this->pdoInsert('users', [ 'login' => $user, 'password' => $pass ]);
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
    return $this->pdoInsert('tracks', [ 'user_id' => $userId, 'name' => $trackName, 'comment' => $comment ]);
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
              VALUES ('$userId', '$trackId', FROM_UNIXTIME($timeStamp), '$latitude', '$longitude')";
    return $this->pdoInsertRaw($query);
  }
}
?>
