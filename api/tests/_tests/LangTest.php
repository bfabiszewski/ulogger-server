<?php
declare(strict_types = 1);

namespace uLogger\Tests\_tests;

use PHPUnit\Framework\TestCase;
use uLogger\Component\Config;
use uLogger\Component\Lang;

class LangTest extends TestCase {

  protected $mockConfig;

  public function setUp(): void {
    parent::setUp();
    $this->mockConfig = new Config(false);
  }

  public function testGetLanguages(): void {
    $languages = Lang::getLanguages();
    self::assertNotEmpty($languages);
    self::assertArrayHasKey("en", $languages);
    self::assertArrayHasKey("pl", $languages);
    self::assertEquals("English", $languages["en"]);
    self::assertEquals("Polski", $languages["pl"]);
  }

  public function testGetStrings(): void {
    $lang = new Lang($this->mockConfig);
    self::assertEquals("User", $lang->getStrings()["user"]);
    $this->mockConfig->lang = "pl";
    $lang = new Lang($this->mockConfig);
    self::assertEquals("Użytkownik", $lang->getStrings()["user"]);
  }

  public function testGetSetupStrings(): void {
    $lang = new Lang($this->mockConfig);
    self::assertEquals("Congratulations!", $lang->getSetupStrings()["congratulations"]);
    $this->mockConfig->lang = "pl";
    $lang = new Lang($this->mockConfig);
    self::assertEquals("Gratulacje!", $lang->getSetupStrings()["congratulations"]);
  }
}

?>
