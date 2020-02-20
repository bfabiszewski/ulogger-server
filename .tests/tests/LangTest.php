<?php
use PHPUnit\Framework\TestCase;

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }

require_once(__DIR__ . "/../../helpers/config.php");
require_once(__DIR__ . "/../../helpers/lang.php");

class LangTest extends TestCase {

  protected $mockConfig;

  public function setUp() {
    parent::setUp();
    $this->mockConfig = new uConfig(false);
  }

  public function testGetLanguages() {
    $languages = uLang::getLanguages();
    $this->assertNotEmpty($languages);
    $this->assertArrayHasKey("en", $languages);
    $this->assertArrayHasKey("pl", $languages);
    $this->assertEquals("English", $languages["en"]);
    $this->assertEquals("Polski", $languages["pl"]);
  }

  public function testGetStrings() {
    $lang = new uLang($this->mockConfig);
    $this->assertEquals("User", $lang->getStrings()["user"]);
    $this->mockConfig->lang = "pl";
    $lang = new uLang($this->mockConfig);
    $this->assertEquals("Użytkownik", $lang->getStrings()["user"]);
  }

  public function testGetSetupStrings() {
    $lang = new uLang($this->mockConfig);
    $this->assertEquals("Congratulations!", $lang->getSetupStrings()["congratulations"]);
    $this->mockConfig->lang = "pl";
    $lang = new uLang($this->mockConfig);
    $this->assertEquals("Gratulacje!", $lang->getSetupStrings()["congratulations"]);
  }
}
?>
