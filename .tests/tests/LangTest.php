<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../helpers/config.php");
require_once(__DIR__ . "/../../helpers/lang.php");

class LangTest extends TestCase {

  public function testGetLanguages() {
    $languages = uLang::getLanguages();
    $this->assertNotEmpty($languages);
    $this->assertArrayHasKey("en", $languages);
    $this->assertArrayHasKey("pl", $languages);
    $this->assertEquals("English", $languages["en"]);
    $this->assertEquals("Polski", $languages["pl"]);
  }

  public function testGetStrings() {
    $lang = new uLang("en");
    $this->assertEquals("User", $lang->getStrings()["user"]);
    $lang = new uLang("pl");
    $this->assertEquals("Użytkownik", $lang->getStrings()["user"]);
  }

  public function testGetSetupStrings() {
    $lang = new uLang("en");
    $this->assertEquals("Congratulations!", $lang->getSetupStrings()["congratulations"]);
    $lang = new uLang("pl");
    $this->assertEquals("Gratulacje!", $lang->getSetupStrings()["congratulations"]);
  }
}
?>
