<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\_lib;

use Error;
use PHPUnit\Framework\TestCase;
use TypeError;

trait AssertExceptionTrait {

  public static function assertError(string $expectedException, callable $test, ?string $message = null): void {
    $messagePrefix = $message ? "$message: " : "";
    try {
      $test();
    } catch (Error $actualException) {
      TestCase::assertInstanceOf($expectedException, $actualException, "{$messagePrefix}Unexpected exception thrown");
      return;
    }

    TestCase::fail("{$messagePrefix}No exception thrown");
  }

  public static function assertTypeError(callable $test, ?string $message = null): void {
    self::assertError(TypeError::class, $test, $message);
  }
}

?>
