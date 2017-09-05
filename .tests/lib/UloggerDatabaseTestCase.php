<?php
use PHPUnit\Framework\TestCase;

require_once("BaseDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/db.php");

class UloggerDatabaseTestCase extends BaseDatabaseTestCase {

  static private $udb = null;

  public static function setUpBeforeClass() {
    parent::setUpBeforeClass();

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

    // uDb connection
    if (self::$udb == null) {
      self::$udb = new ReflectionClass("uDb");
      $dbInstance = self::$udb->getProperty('instance');
      $dbInstance->setAccessible(true);
      $dbInstance->setValue(new uDb($db_host, $db_user, $db_pass, $db_name, $db_port));
    }
  }

  public static function tearDownAfterClass() {
    parent::tearDownAfterClass();
    self::$udb = null;
  }
}
?>
