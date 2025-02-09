<?php
declare(strict_types = 1);

namespace uLogger\Tests\_tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use uLogger\Helper\Utils;

class UtilsTest extends TestCase {

  private $rootDir;

  public function __construct($name) {
    parent::__construct($name);
    $this->rootDir = Utils::getRootDir();
  }

  /**
   * @throws ReflectionException
   */
  public function testGetUploadMaxSize(): void {
    $iniGetBytes = new ReflectionMethod('uLogger\\Helper\\Utils', 'iniGetBytes');
    $iniGetBytes->setAccessible(true);

    ini_set("memory_limit", "1G");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    self::assertEquals(1024 * 1024 * 1024, $result);

    ini_set("memory_limit", 100 . "M");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    self::assertEquals(100 * 1024 * 1024, $result);

    ini_set("memory_limit", 100 * 1024 . "K");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    self::assertEquals(100 * 1024 * 1024, $result);

    ini_set("memory_limit", (string) (100 * 1024 * 1024));
    $result = $iniGetBytes->invoke(null, "memory_limit");
    self::assertEquals(100 * 1024 * 1024, $result);

  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlMain(): void {

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = $this->rootDir . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = Utils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlScript(): void {

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = $this->rootDir . "/utils/test.php";
    $_SERVER["PHP_SELF"] = "/utils/test.php";
    $result = Utils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlSubfolder(): void {

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = $this->rootDir . "/index.php";
    $_SERVER["PHP_SELF"] = "/ulogger/index.php";
    $result = Utils::getBaseUrl();
    $expected = "http://www.example.com/ulogger/";
    self::assertEquals($expected, $result);
  }

  public function testGetBaseUrlHttps(): void {

    $_SERVER["HTTPS"] = "on";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = $this->rootDir . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = Utils::getBaseUrl();
    $expected = "https://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlHttp(): void {

    $_SERVER["HTTPS"] = "off";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = $this->rootDir . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = Utils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);

    unset($_SERVER["HTTPS"]);
    $result = Utils::getBaseUrl();
    self::assertEquals($expected, $result);
  }

  public function testIsAbsolutePath(): void {
    self::assertTrue(Utils::isAbsolutePath("/foo"));
    self::assertTrue(Utils::isAbsolutePath("/foo/bar"));
    self::assertTrue(Utils::isAbsolutePath("/"));
    self::assertTrue(Utils::isAbsolutePath("/."));
    self::assertTrue(Utils::isAbsolutePath("\\"));
    self::assertTrue(Utils::isAbsolutePath("C:\\\\foo"));
    self::assertTrue(Utils::isAbsolutePath("Z:\\\\FOO/BAR"));

    self::assertFalse(Utils::isAbsolutePath("foo"));
    self::assertFalse(Utils::isAbsolutePath("foo/bar"));
    self::assertFalse(Utils::isAbsolutePath("./foo"));
    self::assertFalse(Utils::isAbsolutePath("../"));
    self::assertFalse(Utils::isAbsolutePath(".\\foo"));
  }
}

?>
