<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../helpers/utils.php");

class UtilsTest extends TestCase {

  public function testGetUploadMaxSize() {
    $iniGetBytes = new ReflectionMethod('uUtils', 'iniGetBytes');
    $iniGetBytes->setAccessible(true);

    ini_set("memory_limit", "1G");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    $this->assertEquals(1024 * 1024 * 1024, $result);

    ini_set("memory_limit", 100 . "M");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    $this->assertEquals(100 * 1024 * 1024, $result);

    ini_set("memory_limit", 100 * 1024 . "K");
    $result = $iniGetBytes->invoke(null, "memory_limit");
    $this->assertEquals(100 * 1024 * 1024, $result);

    ini_set("memory_limit", 100 * 1024 * 1024);
    $result = $iniGetBytes->invoke(null, "memory_limit");
    $this->assertEquals(100 * 1024 * 1024, $result);

  }

  public function testGetBaseUrlMain() {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html/ulogger");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    $this->assertEquals($expected, $result);
  }

  public function testGetBaseUrlScript() {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/utils/test.php";
    $_SERVER["PHP_SELF"] = "/utils/test.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    $this->assertEquals($expected, $result);
  }

  public function testGetBaseUrlSubfolder() {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/ulogger/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/ulogger/";
    $this->assertEquals($expected, $result);
  }

  public function testGetBaseUrlHttps() {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "on";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "https://www.example.com/";
    $this->assertEquals($expected, $result);
  }

  public function testGetBaseUrlHttp() {
    if (!defined("ROOT_DIR")) {
      define("ROOT_DIR", "/var/www/html");
    }

    $_SERVER["HTTPS"] = "off";
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $_SERVER["SCRIPT_FILENAME"] = ROOT_DIR . "/index.php";
    $_SERVER["PHP_SELF"] = "/index.php";
    $result = uUtils::getBaseUrl();
    $expected = "http://www.example.com/";
    $this->assertEquals($expected, $result);

    unset($_SERVER["HTTPS"]);
    $this->assertEquals($expected, $result);
  }
}
?>
