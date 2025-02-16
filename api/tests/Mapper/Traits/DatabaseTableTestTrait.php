<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2023 odan, selective/test-traits, The MIT License (MIT)
 * @copyright  2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Traits;

use DomainException;
use PDO;
use PDOException;
use PDOStatement;
use UnexpectedValueException;

/**
 * Database test.
 */
trait DatabaseTableTestTrait {

  /**
   * Asserts that a given table is the same as the given row.
   *
   * @param array $expectedRow Row expected to find
   * @param string $table Table to look into
   * @param int $id The primary key
   * @param array|null $fields The columns
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRow(
    array  $expectedRow,
    string $table,
    int    $id,
    array  $fields = null,
    string $message = ''
  ): void {
    $this->assertSame(
      $expectedRow,
      $this->getTableRowById($table, $id, $fields ?: array_keys($expectedRow)),
      $message
    );
  }

  /**
   * Fetch row by ID.
   *
   * @param string $table Table name
   * @param int $id The primary key value
   * @param array|null $fields The array of fields
   *
   * @return array Row
   * @throws DomainException
   *
   */
  protected function getTableRowById(string $table, int $id, array $fields = null): array {
    $cols = '*';
    if (!empty($fields)) {
      foreach ($fields as &$field) {
        if ($field === 'time') {
          $field = $this->unixTimestamp($field);
        }
      }
      $cols = implode(', ', $fields);
    }
    $sql = "SELECT $cols FROM $table WHERE id = :id";
    $statement = $this->createPreparedStatement($sql);
    $statement->execute([ 'id' => $id ]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (empty($row)) {
      throw new DomainException(sprintf('Row not found: %s', $id));
    }

    if ($this->driver === 'pgsql') {
      foreach ($row as &$value) {
        if (is_bool($value)) {
          $value = (int) $value;
        }
      }
    }

    return $row;
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
   * Fetch row by ID.
   *
   * @param string $table Table name
   * @return array Row
   */
  protected function getTableAllRows(string $table): array {
    $sql = sprintf('SELECT * FROM %s', $table);
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
      $this->insertRow($table, $rowsArr);
      try {
        $ret = (int) $this->pdo->lastInsertId($this->driver === 'pgsql' ? "{$table}_id_seq" : null);
      } catch (PDOException) {
      }
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
   * Asserts that a given table equals the given row.
   *
   * @param array $expectedRow Row expected to find
   * @param string $table Table to look into
   * @param int $id The primary key
   * @param array|null $fields The columns
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRowEquals(
    array  $expectedRow,
    string $table,
    int    $id,
    array  $fields = null,
    string $message = ''
  ): void {
    $this->assertEquals(
      $expectedRow,
      $this->getTableRowById($table, $id, $fields ?: array_keys($expectedRow)),
      $message
    );
  }

  /**
   * Asserts that a given table contains a given row value.
   *
   * @param mixed $expected The expected value
   * @param string $table Table to look into
   * @param int $id The primary key
   * @param string $field The column name
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRowValue(
    mixed  $expected,
    string $table,
    int    $id,
    string $field,
    string $message = ''
  ): void {
    $actual = $this->getTableRowById($table, $id, [ $field ])[$field];
    $this->assertSame($expected, $actual, $message);
  }

  /**
   * Asserts that a given table contains a given number of rows.
   *
   * @param int $expected The number of expected rows
   * @param string $table Table to look into
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRowCount(int $expected, string $table, string $message = ''): void {
    $this->assertSame($expected, $this->getTableRowCount($table), $message);
  }

  /**
   * Get table row count.
   *
   * @param string $table The table name
   *
   * @return int The number of rows
   */
  protected function getTableRowCount(string $table): int {
    $sql = sprintf('SELECT COUNT(*) AS counter FROM %s;', $table);
    $statement = $this->createQueryStatement($sql);
    $row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

    return (int) ($row['counter'] ?? 0);
  }

  /**
   * Asserts that a given table contains a given number of rows.
   *
   * @param string $table Table to look into
   * @param int $id The id
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRowExists(string $table, int $id, string $message = ''): void {
    $this->assertTrue((bool) $this->findTableRowById($table, $id), $message);
  }

  /**
   * Fetch row by ID.
   *
   * @param string $table Table name
   * @param int $id The primary key value
   *
   * @return array Row
   */
  protected function findTableRowById(string $table, int $id): array {
    $sql = sprintf('SELECT * FROM %s WHERE id = :id', $table);
    $statement = $this->createPreparedStatement($sql);
    $statement->execute([ 'id' => $id ]);

    return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
  }

  /**
   * Asserts that a given table contains a given number of rows.
   *
   * @param string $table Table to look into
   * @param int $id The id
   * @param string $message Optional message
   *
   * @return void
   */
  protected function assertTableRowNotExists(string $table, int $id, string $message = ''): void {
    $this->assertFalse((bool) $this->findTableRowById($table, $id), $message);
  }

  /**
   * Get function that converts date time column to unix timestamp for current PDO driver
   * @param string $column Column name or timestamp value
   * @return string
   */
  protected function unixTimestamp(string $column): string {
    switch ($this->driver) {
      default:
      case 'mysql':
        return "UNIX_TIMESTAMP($column) AS $column";
      case 'pgsql':
        return "EXTRACT(EPOCH FROM $column::TIMESTAMP WITH TIME ZONE)::INT AS $column";
      case 'sqlite':
        return "UNIXEPOCH($column) AS $column";
    }
  }

  /**
   * Get function that converts unix timestamp to date time for current PDO driver
   * @param int|string $column Column name or timestamp value
   * @return string
   */
  protected function fromUnixTime(int|string $column): string {
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

  /**
   * @param string $table
   * @param array $row
   * @return void
   */
  protected function insertRow(string $table, array $row): void {
    $values = ':' . implode(', :', array_keys($row));
    $columns = implode(', ', array_keys($row));
    $query = "INSERT INTO $table ($columns) VALUES ($values)";
    $stmt = $this->pdo->prepare($query);
    if ($stmt !== false) {
      $stmt->execute(array_combine(explode(', ', $values), array_values($row)));
    }
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
  private function createPreparedStatement(string $sql): PDOStatement {
    $statement = $this->getConnection()->prepare($sql);

    if (!$statement instanceof PDOStatement) {
      throw new UnexpectedValueException('Invalid SQL statement');
    }

    return $statement;
  }
}
