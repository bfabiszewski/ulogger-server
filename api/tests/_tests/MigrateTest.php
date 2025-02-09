<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\_tests;

use PDO;
use PDOException;
use uLogger\Component\Db;
use uLogger\Helper\Migration;
use uLogger\Tests\_lib\UloggerDatabaseTestCase;

define("SKIP_RUN", true);

class MigrateTest extends UloggerDatabaseTestCase {

  protected function tearDown(): void {
    if ($this->getName() === "testUpdateSchemas") {
      self::runSqlScript(dirname(__DIR__) . "/../public/scripts/ulogger." . $this->getDbDriverName());
    }
    parent::tearDown();
  }

  public function testUpdateSchemas(): void {
    self::runSqlScript(dirname(__DIR__) . "/_fixtures/ulogger_0_6." . $this->getDbDriverName());
    $this->loadDataSet("fixture_0_6.xml");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertNotContains("admin", $this->getConnection()->getMetaData()->getTableColumns("users"));
    self::assertContains("image_id", $this->getConnection()->getMetaData()->getTableColumns("positions"));
    self::assertNotContains("ol_layers", $this->getConnection()->getMetaData()->getTableNames());
    self::assertNotContains("config", $this->getConnection()->getMetaData()->getTableNames());
    $this->setOutputCallback(static function() {});
    $ret = Migration::updateSchemas();
    $this->resetConnection();
    self::assertTrue($ret, "Function updateSchemas() failed");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount("positions"), "Wrong row count");
    self::assertContains("admin", $this->getConnection()->getMetaData()->getTableColumns("users"), "Missing table column");
    self::assertContains("image", $this->getConnection()->getMetaData()->getTableColumns("positions"), "Missing table column");
    self::assertContains("ol_layers", $this->getConnection()->getMetaData()->getTableNames(), "Missing table");
    self::assertContains("config", $this->getConnection()->getMetaData()->getTableNames(), "Missing table");
  }

  public function testUpdateConfig(): void {
    $this->loadDataSet("fixture_non_admin.xml");
    $this->setOutputCallback(static function () { });
    $ret = Migration::updateConfig(dirname(__DIR__) . "/_fixtures/config_0_6.php");
    self::assertTrue($ret, "Function updateConfig() failed");
    // admin user imported from config file
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue((bool) $this->pdoGetColumn("SELECT admin FROM users WHERE login = 'admin'"), "User should be admin");
    // settings imported from config file
    $expected = [ "config" => [
      [ "name" => "color_extra", "value" => "s:7:\"#cccccc\";" ], // default
      [ "name" => "color_hilite", "value" => "s:7:\"#feff6a\";" ], // default
      [ "name" => "color_normal", "value" => "s:7:\"#ffffff\";" ], // default
      [ "name" => "color_start", "value" => "s:7:\"#55b500\";" ], // default
      [ "name" => "color_stop", "value" => "s:7:\"#ff6a00\";" ], // default
      [ "name" => "google_key", "value" => "s:13:\"testGoogleKey\";" ],
      [ "name" => "interval_seconds", "value" => "i:1234;" ],
      [ "name" => "lang", "value" => "s:2:\"pl\";" ],
      [ "name" => "latitude", "value" => "d:12.34;" ],
      [ "name" => "longitude", "value" => "d:12.34;" ],
      [ "name" => "map_api", "value" => "s:7:\"testApi\";" ],
      [ "name" => "pass_lenmin", "value" => "i:12;" ],
      [ "name" => "pass_strength", "value" => "i:2;" ],
      [ "name" => "public_tracks", "value" => "b:0;" ],
      [ "name" => "require_auth", "value" => "b:1;" ],
      [ "name" => "stroke_color", "value" => "s:7:\"#abcdef\";" ],
      [ "name" => "stroke_opacity", "value" => "i:1;" ],
      [ "name" => "stroke_weight", "value" => "i:22;" ],
      [ "name" => "units", "value" => "s:8:\"imperial\";" ],
      [ "name" => "upload_maxsize", "value" => "i:5242880;" ]
    ] ];
    $actual = $this->getConnection()->createQueryTable(
      "config",
      "SELECT name, " . Db::getInstance()->from_lob("value") . " FROM config ORDER BY name"
    );
    $expected = $this->createArrayDataSet($expected)->getTable("config");
    self::assertTablesEqual($expected, $actual);
    // layers imported from config file
    self::assertEquals(1, $this->getConnection()->getRowCount("ol_layers"), "Wrong row count");
    $expected = [ "id" => 1, "name" => "TestLayer", "url" => "https://test_tile.png", "priority" => 0 ];
    $actual = $this->getConnection()->createQueryTable(
      "ol_layers",
      "SELECT * FROM ol_layers"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testWaitForUser(): void {
    $this->setOutputCallback(static function () { });
    $yes = tmpfile();
    fwrite($yes, "yes");
    $ret = Migration::waitForUser(stream_get_meta_data($yes)['uri']);
    fclose($yes);
    self::assertTrue($ret, "Wrong return status");

    $no = tmpfile();
    fwrite($no, "no");
    $ret = Migration::waitForUser(stream_get_meta_data($no)['uri']);
    fclose($no);
    self::assertFalse($ret, "Wrong return status");
  }

  /**
   * Run SQL commands from file.
   * Basic subset only. Multiple commands must not be on the same line.
   * @param string $path Script path
   * @throws PDOException
   */
  private static function runSqlScript(string $path): void {
    $script = file_get_contents($path);
    $count = preg_match_all('/^(?:(?:DROP|CREATE) (?:TABLE|INDEX)|INSERT|PRAGMA|SET) .*?;\s*$/smi', $script, $queries);
    if ($count) {
      try {
        Db::getInstance()->beginTransaction();
        foreach ($queries[0] as $query) {
          Db::getInstance()->exec($query);
        }
        // make sure the transaction wasn't autocommitted
        if (Db::getInstance()->inTransaction()) {
          Db::getInstance()->commit();
        }
      } catch (PDOException $e) {
        if (Db::getInstance()->inTransaction()) {
          Db::getInstance()->rollBack();
        }
        throw $e;
      }
    }
  }

  private function getDbDriverName() {
    return Db::getInstance()->getAttribute(PDO::ATTR_DRIVER_NAME);
  }

  private function loadDataSet($name): void {
    $this->resetAutoincrement();
    $dataSet = $this->createFlatXMLDataSet(dirname(__DIR__) . '/_fixtures/' . $name);
    $this->getDatabaseTester()->setDataSet($dataSet);
    $this->getDatabaseTester()->onSetUp();
  }
}

?>
