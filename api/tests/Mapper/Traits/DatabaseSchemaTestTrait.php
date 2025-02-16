<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2023 odan, selective/test-traits, The MIT License (MIT)
 * @copyright  2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Traits;

use Exception;
use PDO;
use PDOStatement;
use UnexpectedValueException;
use function PHPUnit\Framework\assertInstanceOf;

trait DatabaseSchemaTestTrait {
  /**
   * @var string Path to schema.sql
   */
  protected string $schemaFile = '';

  /**
   * Create tables and insert fixtures.
   *
   * TestCases must call this method inside setUp().
   *
   * @param string|null $schemaFile The sql schema file
   *
   * @return void
   */
  protected function setUpDatabase(string $schemaFile = null): void {
    if (isset($schemaFile)) {
      $this->schemaFile = $schemaFile;
    }

    assertInstanceOf(PDO::class, $this->getConnection());

    $this->createTables();
    $this->truncateTables();
    $this->resetAutoincrement();

    if (!empty($this->fixtures)) {
      $this->insertFixtures($this->fixtures);
    }
  }

  /**
   * Create tables.
   *
   * @return void
   */
  protected function createTables(): void {
    if (defined('DB_TEST_TRAIT_INIT')) {
      return;
    }

    $this->dropTables();
    $this->importSchema();

    define('DB_TEST_TRAIT_INIT', 1);
  }

  /**
   * Get database variable.
   *
   * @param string $variable The variable
   *
   * @return string|null The value
   */
  protected function getDatabaseVariable(string $variable): ?string {
    $statement = $this->getConnection()->prepare('SHOW VARIABLES LIKE ?');
    if (!$statement || $statement->execute([ $variable ]) === false) {
      throw new UnexpectedValueException('Invalid SQL statement');
    }

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
      // Database variable not defined
      return null;
    }

    return (string) $row['Value'];
  }


  /**
   * Clean up database. Truncate tables.
   *
   * @return void
   */
  protected function dropTables(): void {
    $pdo = $this->getConnection();

    $this->setTableChecks(false);

    $sql = $this->getTableNamesQuery();
    $statement = $this->createQueryStatement($sql);

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    $sql = [];
    foreach ($rows as $row) {
      $sql[] = $this->getDropQuery($row['name']);
    }

    if ($sql) {
      $pdo->exec(implode("\n", $sql));
    }
    $this->setTableChecks(true);
  }

  /**
   * Create PDO statement.
   *
   * @param string $sql The sql
   *
   * @return PDOStatement The statement
   * @throws UnexpectedValueException
   *
   */
  private function createQueryStatement(string $sql): PDOStatement {
    $statement = $this->getConnection()->query($sql, PDO::FETCH_ASSOC);

    if (!$statement instanceof PDOStatement) {
      throw new UnexpectedValueException('Invalid SQL statement');
    }

    return $statement;
  }

  /**
   * Import table schema.
   *
   * @return void
   * @throws UnexpectedValueException
   */
  protected function importSchema(): void {
    if (!$this->schemaFile) {
      throw new UnexpectedValueException('The path for schema.sql is not defined');
    }

    if (!file_exists($this->schemaFile)) {
      throw new UnexpectedValueException(sprintf('File not found: %s', $this->schemaFile));
    }

    $pdo = $this->getConnection();
    $this->setTableChecks(false);
    $pdo->exec((string) file_get_contents($this->schemaFile));
    $this->setTableChecks(true);
  }

  /**
   * Clean up database.
   *
   * @return void
   */
  protected function truncateTables(): void {
    $pdo = $this->getConnection();

    $this->setTableChecks(false);

    $tableNamesQuery = $this->getTableNamesQuery();
    $statement = $this->createQueryStatement(
      $tableNamesQuery
    );
    if ($this->driver === 'mysql') {
      $expiry = $this->getDatabaseVariable('information_schema_stats_expiry');
      if ($expiry === null) {
        // MariaDB: Truncate only changed tables
        $statement = $this->createQueryStatement(
          $tableNamesQuery . ' AND (update_time IS NOT NULL OR auto_increment IS NOT NULL)'
        );
      }
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    $sql = [];
    foreach ($rows as $row) {
      $sql[] = $this->getTruncateQuery($row['name']);
    }

    if ($sql) {
      $pdo->exec(implode("\n", $sql));
    }

    $this->setTableChecks(true);
  }

  /**
   * Iterate over all fixtures and insert them into their tables.
   *
   * @param array $fixtures The fixtures
   *
   * @return void
   */
  protected function insertFixtures(array $fixtures): void {
    foreach ($fixtures as $fixture) {
      $object = new $fixture();

      foreach ($object->records as $row) {
        $this->insertFixture($object->table, $row);
      }
      if ($this->driver === 'pgsql') {
        $this->pdo->exec("ALTER SEQUENCE IF EXISTS {$object->table}_id_seq RESTART WITH " . count($object->records) + 1);
      } elseif ($this->driver === 'sqlite') {
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = '$object->table'");
      }
    }
  }

  /**
   * Insert row into table.
   *
   * @param string $table The table name
   * @param array $row The row data
   */
  protected function insertFixture(string $table, array $row): void {
    if (!empty($row)) {
      $this->insertRow($table, $row);
    }
  }

  /**
   * @return string
   */
  private function getTableNamesQuery(): string {
    $query = null;
    if ($this->driver === 'mysql') {
      $query = 'SELECT TABLE_NAME as name
                FROM information_schema.tables
                WHERE table_schema = database()';
    } elseif ($this->driver === 'pgsql') {
      $query = "SELECT table_name as name
            FROM information_schema.tables
            WHERE table_schema = 'public' AND table_type = 'BASE TABLE'";
    } elseif ($this->driver === 'sqlite') {
      $query = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';";
    }
    return $query;
  }

  /**
   * @param bool $isOn
   * @return void
   */
  private function setTableChecks(bool $isOn): void {
    $value = $isOn ? 1 : 0;
    $pdo = $this->getConnection();
    if ($this->driver === 'mysql') {
      $pdo->exec("SET unique_checks=$value; SET foreign_key_checks=$value;");
    }
  }

  /**
   * @param string $tableName
   * @return string
   */
  private function getDropQuery(string $tableName): string {
    $query = "DROP TABLE `$tableName`;";
    if ($this->driver === 'pgsql') {
      $query = "DROP TABLE IF EXISTS \"$tableName\" CASCADE;";
    }
    return $query;
  }

  /**
   * @param $tableName
   * @return string
   */
  private function getTruncateQuery($tableName): string {
    $query = "TRUNCATE TABLE `$tableName`;";
    if ($this->driver === 'pgsql') {
      $query = "TRUNCATE TABLE \"$tableName\" CASCADE;";
    } elseif ($this->driver === 'sqlite') {
      $query = "DELETE FROM $tableName;";
    }
    return $query;
  }

  /**
   * @param int $users
   * @param int $tracks
   * @param int $positions
   * @param int $layers
   * @return void
   */
  protected function resetAutoincrement(int $users = 1, int $tracks = 1, int $positions = 1, int $layers = 1): void {
    if ($this->driver === 'pgsql') {
      $this->pdo->exec("ALTER SEQUENCE IF EXISTS users_id_seq RESTART WITH $users");
      $this->pdo->exec("ALTER SEQUENCE IF EXISTS tracks_id_seq RESTART WITH $tracks");
      $this->pdo->exec("ALTER SEQUENCE IF EXISTS positions_id_seq RESTART WITH $positions");
      $this->pdo->exec("ALTER SEQUENCE IF EXISTS ol_layers_id_seq RESTART WITH $layers");
    } elseif ($this->driver === 'sqlite') {
      $retry = 1;
      do {
        try {
          $this->pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'users'");
          $this->pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'tracks'");
          $this->pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'positions'");
          $this->pdo->exec("DELETE FROM sqlite_sequence WHERE NAME = 'ol_layers'");
          $retry = 0;
        } catch (Exception $e) {
          // sqlite raises error when db schema changes in another connection.
          if (str_contains($e->getMessage(), 'database schema has changed')) {
            self::setUpBeforeClass();
          }
        }
      } while ($retry--);
    }
  }
}
