<?php
use PHPUnit\Framework\TestCase;

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }

require_once(__DIR__ . "/../../helpers/config.php");
require_once(__DIR__ . "/../../helpers/lang.php");

class LangTest extends TestCase {

  protected $mockConfig;

  public function setUp(): void {
    parent::setUp();
    $this->mockConfig = new uConfig(false);
  }

  public function testGetLanguages(): void {
    $languages = uLang::getLanguages();
    self::assertNotEmpty($languages);
    self::assertArrayHasKey("en", $languages);
    self::assertArrayHasKey("pl", $languages);
    self::assertEquals("English", $languages["en"]);
    self::assertEquals("Polski", $languages["pl"]);
  }

  public function testGetStrings(): void {
    $lang = new uLang($this->mockConfig);
    self::assertEquals("User", $lang->getStrings()["user"]);
    $this->mockConfig->lang = "pl";
    $lang = new uLang($this->mockConfig);
    self::assertEquals("Użytkownik", $lang->getStrings()["user"]);
  }

  public function testGetSetupStrings(): void {
    $lang = new uLang($this->mockConfig);
    self::assertEquals("Congratulations!", $lang->getSetupStrings()["congratulations"]);
    $this->mockConfig->lang = "pl";
    $lang = new uLang($this->mockConfig);
    self::assertEquals("Gratulacje!", $lang->getSetupStrings()["congratulations"]);
  }
}
?>
