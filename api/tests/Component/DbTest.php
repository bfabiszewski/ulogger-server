<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Component;

use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use uLogger\Component\Db;
use uLogger\Helper\Utils;

/**
 * @runTestsInSeparateProcesses
 */
final class DbTest extends TestCase {
  /** @var Db */
  private Db $db;

  protected function setUp(): void {
    // Create an in‑memory SQLite database.
    $this->db = new Db('sqlite::memory:', '', '');
  }

  public function testConstructorSetsDriverAndTables(): void {
    // For an in‑memory SQLite DB, the driver should be 'sqlite'.
    $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $this->assertSame('sqlite', $driver);

    // With no prefix (default is empty), table('users') should return "users", etc.
    $this->assertSame('users', $this->db->table('users'));
    $this->assertSame('tracks', $this->db->table('tracks'));
  }

  public function testGetDbName(): void {
    $mysqlDsn = 'mysql:dbname=testdb;host=localhost';
    $pgsqlDsn = 'pgsql:dbname=testdb;host=localhost';
    $sqliteDsn = 'sqlite::memory:';

    $this->assertSame('testdb', Db::getDbName($mysqlDsn));
    $this->assertSame('testdb', Db::getDbName($pgsqlDsn));
    $this->assertSame(':memory:', Db::getDbName($sqliteDsn));
  }

  public function testNormalizeDsnNonSqlite(): void {
    $dsn = 'mysql:dbname=testdb;host=localhost';
    $this->assertSame($dsn, Db::normalizeDsn($dsn));
  }

  public function testNormalizeDsnWithAbsolutePath(): void {
    // If an absolute path is provided for a SQLite DSN, it should return the same DSN.
    $absolutePath = __DIR__ . '/db.sqlite';
    $dsn = "sqlite:$absolutePath";
    $this->assertSame($dsn, Db::normalizeDsn($dsn));
  }

  public function testNormalizeDsnWithRelativePath(): void {
    // For a relative path, normalization should convert it to an absolute path.
    $relativePath = 'relative/path/to/db.sqlite';
    $dsn = "sqlite:$relativePath";
    $normalized = Db::normalizeDsn($dsn);

    $this->assertStringStartsWith('sqlite:', $normalized);
    $pathPart = substr($normalized, strlen('sqlite:'));
    $this->assertTrue(Utils::isAbsolutePath($pathPart));
  }

  public function testUnixTimestamp(): void {
    // Use Reflection to override the private static driver property.
    $refClass = new ReflectionClass(Db::class);
    $driverProp = $refClass->getProperty('driver');

    // For MySQL:
    $driverProp->setValue(null, 'mysql');
    $this->assertSame('UNIX_TIMESTAMP(col)', $this->db->unixTimestamp('col'));

    // For PostgreSQL:
    $driverProp->setValue(null, 'pgsql');
    $this->assertSame('EXTRACT(EPOCH FROM col::TIMESTAMP WITH TIME ZONE)', $this->db->unixTimestamp('col'));

    // For SQLite:
    $driverProp->setValue(null, 'sqlite');
    $this->assertSame("STRFTIME('%s', col)", $this->db->unixTimestamp('col'));
  }

  public function testLobPlaceholder(): void {
    $refClass = new ReflectionClass(Db::class);
    $driverProp = $refClass->getProperty('driver');

    $driverProp->setValue(null, 'mysql');
    $this->assertSame('?', $this->db->lobPlaceholder());

    $driverProp->setValue(null, 'pgsql');
    $this->assertSame('?::bytea', $this->db->lobPlaceholder());

    $driverProp->setValue(null, 'sqlite');
    $this->assertSame('?', $this->db->lobPlaceholder());
  }

  public function testFromLob(): void {
    $refClass = new ReflectionClass(Db::class);
    $driverProp = $refClass->getProperty('driver');

    $driverProp->setValue(null, 'mysql');
    $this->assertSame('col', $this->db->fromLob('col'));

    $driverProp->setValue(null, 'pgsql');
    $this->assertSame("encode(col, 'escape') AS col", $this->db->fromLob('col'));

    $driverProp->setValue(null, 'sqlite');
    $this->assertSame('col', $this->db->fromLob('col'));
  }

  public function testFromUnixTime(): void {
    $refClass = new ReflectionClass(Db::class);
    $driverProp = $refClass->getProperty('driver');

    $driverProp->setValue(null, 'mysql');
    $this->assertSame('FROM_UNIXTIME(col)', $this->db->fromUnixTime('col'));

    $driverProp->setValue(null, 'pgsql');
    $this->assertSame('TO_TIMESTAMP(col)', $this->db->fromUnixTime('col'));

    $driverProp->setValue(null, 'sqlite');
    $this->assertSame("DATETIME(col, 'unixepoch')", $this->db->fromUnixTime('col'));
  }

  public function testInsertOrReplace(): void {
    // Prepare test inputs.
    $table = 'users';
    $columns = [ 'id', 'name' ];
    $values = [
      [ '1', "'John'" ],
      [ '2', "'Doe'" ]
    ];
    $key = 'id';
    $update = 'name';

    $refClass = new ReflectionClass(Db::class);
    $driverProp = $refClass->getProperty('driver');

    // For MySQL:
    $driverProp->setValue(null, 'mysql');
    $mysqlSql = $this->db->insertOrReplace($table, $columns, $values, $key, $update);
    $this->assertStringContainsString('INSERT INTO', $mysqlSql);
    $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $mysqlSql);

    // For PostgreSQL:
    $driverProp->setValue(null, 'pgsql');
    $pgsqlSql = $this->db->insertOrReplace($table, $columns, $values, $key, $update);
    $this->assertStringContainsString("ON CONFLICT ($key) DO UPDATE SET", $pgsqlSql);

    // For SQLite:
    $driverProp->setValue(null, 'sqlite');
    $sqliteSql = $this->db->insertOrReplace($table, $columns, $values, $key, $update);
    $this->assertStringContainsString('REPLACE INTO', $sqliteSql);
  }

  public function testTableWithPrefix(): void {
    // Use Reflection to set the private static $prefix property.
    $refClass = new ReflectionClass(Db::class);
    $prefixProp = $refClass->getProperty('prefix');
    $prefixProp->setValue(null, 'ulogger_');

    // Re‑instantiate Db so that initTables() uses the new prefix.
    $dbWithPrefix = new Db('sqlite::memory:', '', '');
    $this->assertSame('ulogger_users', $dbWithPrefix->table('users'));
    $this->assertSame('ulogger_tracks', $dbWithPrefix->table('tracks'));
  }
}
