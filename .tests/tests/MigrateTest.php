<?php
/**
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

define("SKIP_RUN", true);
require_once(dirname(__DIR__) . "/../scripts/migrate_to_1_x.php");
require_once(dirname(__DIR__) . "/lib/UloggerDatabaseTestCase.php");

class MigrateTest extends UloggerDatabaseTestCase {

  protected function tearDown() {
    if ($this->getName() === "testUpdateSchemas") {
      self::runSqlScript(dirname(__DIR__) . "/../scripts/ulogger." . $this->getDbDriverName());
    }
    parent::tearDown();
  }

  public function testUpdateSchemas() {
    self::runSqlScript(dirname(__DIR__) . "/fixtures/ulogger_0_6." . $this->getDbDriverName());
    $this->loadDataSet("fixture_0_6.xml");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertNotContains("admin", $this->getConnection()->getMetaData()->getTableColumns("users"));
    $this->assertContains("image_id", $this->getConnection()->getMetaData()->getTableColumns("positions"));
    $this->assertNotContains("ol_layers", $this->getConnection()->getMetaData()->getTableNames());
    $this->assertNotContains("config", $this->getConnection()->getMetaData()->getTableNames());
    $this->setOutputCallback(static function() {});
    $ret = updateSchemas();
    $this->resetConnection();
    $this->assertTrue($ret, "Function updateSchemas() failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount("positions"), "Wrong row count");
    $this->assertContains("admin", $this->getConnection()->getMetaData()->getTableColumns("users"), "Missing table column");
    $this->assertContains("image", $this->getConnection()->getMetaData()->getTableColumns("positions"), "Missing table column");
    $this->assertContains("ol_layers", $this->getConnection()->getMetaData()->getTableNames(), "Missing table");
    $this->assertContains("config", $this->getConnection()->getMetaData()->getTableNames(), "Missing table");
  }

  public function testUpdateConfig() {
    $this->loadDataSet("fixture_non_admin.xml");
    $this->setOutputCallback(static function() {});
    $ret = updateConfig(dirname(__DIR__) . "/fixtures/config_0_6.php");
    $this->assertTrue($ret, "Function updateConfig() failed");
    // admin user imported from config file
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue((bool) $this->pdoGetColumn("SELECT admin FROM users WHERE login = 'admin'"), "User should be admin");
    // settings imported from config file
    $expected = [ "config" => [
      ["name" => "color_extra", "value" => "s:7:\"#cccccc\";"], // default
      ["name" => "color_hilite", "value" => "s:7:\"#feff6a\";"], // default
      ["name" => "color_normal", "value" => "s:7:\"#ffffff\";"], // default
      ["name" => "color_start", "value" => "s:7:\"#55b500\";"], // default
      ["name" => "color_stop", "value" => "s:7:\"#ff6a00\";"], // default
      ["name" => "google_key", "value" => "s:13:\"testGoogleKey\";"],
      ["name" => "interval_seconds", "value" => "i:1234;"],
      ["name" => "lang", "value" => "s:2:\"pl\";"],
      ["name" => "latitude", "value" => "d:12.34;"],
      ["name" => "longitude", "value" => "d:12.34;"],
      ["name" => "map_api", "value" => "s:7:\"testApi\";"],
      ["name" => "pass_lenmin", "value" => "i:12;"],
      ["name" => "pass_strength", "value" => "i:2;"],
      ["name" => "public_tracks", "value" => "b:0;"],
      ["name" => "require_auth", "value" => "b:1;"],
      ["name" => "stroke_color", "value" => "s:7:\"#abcdef\";"],
      ["name" => "stroke_opacity", "value" => "i:1;"],
      ["name" => "stroke_weight", "value" => "i:22;"],
      ["name" => "units", "value" => "s:8:\"imperial\";"],
      ["name" => "upload_maxsize", "value" => "i:5242880;"]
    ]];
    $actual = $this->getConnection()->createQueryTable(
      "config",
      "SELECT name, " . uDb::getInstance()->from_lob("value") . " FROM config ORDER BY name"
    );
    $expected = $this->createArrayDataSet($expected)->getTable("config");
    self::assertTablesEqual($expected, $actual);
    // layers imported from config file
    $this->assertEquals(1, $this->getConnection()->getRowCount("ol_layers"), "Wrong row count");
    $expected = [ "id" => 1, "name" => "TestLayer", "url" => "https://test_tile.png", "priority" => 0 ];
    $actual = $this->getConnection()->createQueryTable(
      "ol_layers",
      "SELECT * FROM ol_layers"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testWaitForUser() {
    $this->setOutputCallback(static function() {});
    $yes = tmpfile();
    fwrite($yes, "yes");
    $ret = waitForUser(stream_get_meta_data($yes)['uri']);
    fclose($yes);
    $this->assertTrue($ret, "Wrong return status");

    $no = tmpfile();
    fwrite($no, "no");
    $ret = waitForUser(stream_get_meta_data($no)['uri']);
    fclose($no);
    $this->assertFalse($ret, "Wrong return status");
  }

  /**
   * Run SQL commands from file.
   * Basic subset only. Multiple commands must not be on the same line.
   * @param string $path Script path
   * @throws PDOException
   */
  private static function runSqlScript($path) {
    $script = file_get_contents($path);
    $count = preg_match_all('/^(?:(?:DROP|CREATE) (?:TABLE|INDEX)|INSERT|PRAGMA|SET) .*?;\s*$/smi', $script, $queries);
    if ($count) {
      try {
        uDb::getInstance()->beginTransaction();
        foreach ($queries[0] as $query) {
          uDb::getInstance()->exec($query);
        }
        uDb::getInstance()->commit();
      } catch (PDOException $e) {
        uDb::getInstance()->rollBack();
        throw $e;
      }
    }
  }

  private function getDbDriverName() {
    return uDb::getInstance()->getAttribute(PDO::ATTR_DRIVER_NAME);
  }

  private function loadDataSet($name) {
    $this->resetAutoincrement();
    $dataSet = $this->createFlatXMLDataSet(dirname(__DIR__) . '/fixtures/' . $name);
    $this->getDatabaseTester()->setDataSet($dataSet);
    $this->getDatabaseTester()->onSetUp();
  }
}

?>
