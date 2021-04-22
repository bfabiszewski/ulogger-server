<?php

require_once("BaseDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/db.php");

class UloggerDatabaseTestCase extends BaseDatabaseTestCase {

  /**
   * @var uDb $udb
   */
  static private $udb = null;

  /**
   * @throws ReflectionException
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();

    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
      $dotenv->load();
      $dotenv->required(['DB_DSN', 'DB_USER', 'DB_PASS']);
    }

    $db_dsn = $_ENV['DB_DSN'];
    $db_user = $_ENV['DB_USER'];
    $db_pass = $_ENV['DB_PASS'];

    // uDb connection
    if (self::$udb == null) {
      self::$udb = new ReflectionClass('uDb');
      $dbInstance = self::$udb->getProperty('instance');
      $dbInstance->setAccessible(true);
      $dbInstance->setValue(new uDb($db_dsn, $db_user, $db_pass));
    }
  }

  public static function tearDownAfterClass(): void {
    parent::tearDownAfterClass();
    self::$udb = null;
  }
}
?>
