<?php

/**
 * @package    μlogger
 * @copyright  2023 odan, selective/test-traits, The MIT License (MIT)
 * @copyright  2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Traits;

use PDO;
use PDOStatement;
use UnexpectedValueException;

trait DatabaseSchemaTestTrait
{
    /**
     * @var string Path to schema.sql
     */
    protected $schemaFile = '';

    /**
     * Create tables and insert fixtures.
     *
     * TestCases must call this method inside setUp().
     *
     * @param string|null $schemaFile The sql schema file
     *
     * @return void
     */
    protected function setUpDatabase(string $schemaFile = null): void
    {
        if (isset($schemaFile)) {
            $this->schemaFile = $schemaFile;
        }

        $this->getConnection();

        $this->createTables();
        $this->truncateTables();

        if (!empty($this->fixtures)) {
            $this->insertFixtures($this->fixtures);
        }
    }

    /**
     * Create tables.
     *
     * @return void
     */
    protected function createTables(): void
    {
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
    protected function getDatabaseVariable(string $variable): ?string
    {
        $statement = $this->getConnection()->prepare('SHOW VARIABLES LIKE ?');
        if (!$statement || $statement->execute([$variable]) === false) {
            throw new UnexpectedValueException('Invalid SQL statement');
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            // Database variable not defined
            return null;
        }

        return (string)$row['Value'];
    }

    /**
     * Clean up database. Truncate tables.
     *
     * @return void
     */
    protected function dropTables(): void
    {
        $pdo = $this->getConnection();

        $pdo->exec('SET unique_checks=0; SET foreign_key_checks=0;');

        $statement = $this->createQueryStatement(
            'SELECT TABLE_NAME
                FROM information_schema.tables
                WHERE table_schema = database()'
        );

        $rows = (array)$statement->fetchAll(PDO::FETCH_ASSOC);

        $sql = [];
        foreach ($rows as $row) {
            $sql[] = sprintf('DROP TABLE `%s`;', $row['TABLE_NAME']);
        }

        if ($sql) {
            $pdo->exec(implode("\n", $sql));
        }

        $pdo->exec('SET unique_checks=1; SET foreign_key_checks=1;');
    }

    /**
     * Create PDO statement.
     *
     * @param string $sql The sql
     *
     * @throws UnexpectedValueException
     *
     * @return PDOStatement The statement
     */
    private function createQueryStatement(string $sql): PDOStatement
    {
        $statement = $this->getConnection()->query($sql, PDO::FETCH_ASSOC);

        if (!$statement instanceof PDOStatement) {
            throw new UnexpectedValueException('Invalid SQL statement');
        }

        return $statement;
    }

    /**
     * Import table schema.
     *
     * @throws UnexpectedValueException
     *
     * @return void
     */
    protected function importSchema(): void
    {
        if (!$this->schemaFile) {
            throw new UnexpectedValueException('The path for schema.sql is not defined');
        }

        if (!file_exists($this->schemaFile)) {
            throw new UnexpectedValueException(sprintf('File not found: %s', $this->schemaFile));
        }

        $pdo = $this->getConnection();
        $pdo->exec('SET unique_checks=0; SET foreign_key_checks=0;');
        $pdo->exec((string)file_get_contents($this->schemaFile));
        $pdo->exec('SET unique_checks=1; SET foreign_key_checks=1;');
    }

    /**
     * Clean up database.
     *
     * @return void
     */
    protected function truncateTables(): void
    {
        $pdo = $this->getConnection();

        $pdo->exec('SET unique_checks=0; SET foreign_key_checks=0;');

        $expiry = $this->getDatabaseVariable('information_schema_stats_expiry');
        if ($expiry === null) {
            // MariaDB: Truncate only changed tables
            $statement = $this->createQueryStatement(
                'SELECT TABLE_NAME
                FROM information_schema.tables
                WHERE table_schema = database()
                AND (update_time IS NOT NULL OR auto_increment IS NOT NULL)'
            );
        } else {
            // MySQL: Truncate all tables
            // Workaround for MySQL 8: update_time not working.
            // Even SET information_schema_stats_expiry=0; has no affect anymore.
            // https://bugs.mysql.com/bug.php?id=95407
            $statement = $this->createQueryStatement(
                'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = database()'
            );
        }

        $rows = (array)$statement->fetchAll(PDO::FETCH_ASSOC);

        $sql = [];
        foreach ($rows as $row) {
            $sql[] = sprintf('TRUNCATE TABLE `%s`;', $row['TABLE_NAME']);
        }

        if ($sql) {
            $pdo->exec(implode("\n", $sql));
        }

        $pdo->exec('SET unique_checks=1; SET foreign_key_checks=1;');
    }

    /**
     * Iterate over all fixtures and insert them into their tables.
     *
     * @param array $fixtures The fixtures
     *
     * @return void
     */
    protected function insertFixtures(array $fixtures): void
    {
        foreach ($fixtures as $fixture) {
            $object = new $fixture();

            foreach ($object->records as $row) {
                $this->insertFixture($object->table, $row);
            }
        }
    }

    /**
     * Insert row into table.
     *
     * @param string $table The table name
     * @param array $row The row data
     *
     * @return int|null last insert id of auto increment column otherwise null
     */
    protected function insertFixture(string $table, array $row): ?int
    {
        $fields = array_keys($row);

        array_walk(
            $fields,
            function (&$value) {
                $value = sprintf('`%s`=:%s', $value, $value);
            }
        );

        $statement = $this->createPreparedStatement(sprintf('INSERT INTO `%s` SET %s', $table, implode(',', $fields)));
        $statement->execute($row);

        $lastInsertId = $this->getConnection()->lastInsertId();

        return $lastInsertId !== false ? (int)$lastInsertId : null;
    }
}
