<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../helpers/utils.php");

class UtilsTest extends TestCase {

  /**
   * @throws ReflectionException
   */
  public function testGetUploadMaxSize(): void {
    $iniGetBytes = new ReflectionMethod('uUtils', 'iniGetBytes');
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

    ini_set("memory_limit", 100 * 1024 * 1024);
    $result = $iniGetBytes->invoke(null, "memory_limit");
    self::assertEquals(100 * 1024 * 1024, $result);

  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlMain(): void {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html/ulogger");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlScript(): void {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/utils/test.php";
    $_SERVER["PHP_SELF"] = "/utils/test.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlSubfolder(): void {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/ulogger/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/ulogger/";
    self::assertEquals($expected, $result);
  }

  public function testGetBaseUrlHttps(): void {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "on";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "https://www.example.com/";
    self::assertEquals($expected, $result);
  }

  /** @noinspection HttpUrlsUsage */
  public function testGetBaseUrlHttp(): void {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "off";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    self::assertEquals($expected, $result);

    unset($_SERVER["HTTPS"]);
    self::assertEquals($expected, $result);
  }

  public function testIsAbsolutePath(): void {
    self::assertTrue(uUtils::isAbsolutePath("/foo"));
    self::assertTrue(uUtils::isAbsolutePath("/foo/bar"));
    self::assertTrue(uUtils::isAbsolutePath("/"));
    self::assertTrue(uUtils::isAbsolutePath("/."));
    self::assertTrue(uUtils::isAbsolutePath("\\"));
    self::assertTrue(uUtils::isAbsolutePath("C:\\\\foo"));
    self::assertTrue(uUtils::isAbsolutePath("Z:\\\\FOO/BAR"));

    self::assertFalse(uUtils::isAbsolutePath("foo"));
    self::assertFalse(uUtils::isAbsolutePath("foo/bar"));
    self::assertFalse(uUtils::isAbsolutePath("./foo"));
    self::assertFalse(uUtils::isAbsolutePath("../"));
    self::assertFalse(uUtils::isAbsolutePath(".\\foo"));
  }
}
?>
