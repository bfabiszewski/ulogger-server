<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2023 odan, selective/test-traits, The MIT License (MIT)
 * @copyright  2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Traits;

use PDO;

trait DatabaseConnectionTestTrait {
  /**
   * Get database connection.
   *
   * @return PDO The PDO instance
   */
  abstract protected function getConnection(): PDO;

}
