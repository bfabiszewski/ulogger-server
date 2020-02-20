<?php

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(__DIR__ . "/../../helpers/config.php");
require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");

class ConfigTest extends UloggerDatabaseTestCase {
  
  private $config;
  
  private $mapApi;
  private $latitude;
  private $longitude;
  private $googleKey;
  private $requireAuth;
  private $publicTracks;
  private $passLenMin;
  private $passStrength;
  private $interval;
  private $lang;
  private $units;
  private $strokeWeight;
  private $strokeColor;
  private $strokeOpacity;
  private $testLayer;
  private $testUrl;
  private $testPriority;

  public function setUp() {
    parent::setUp();
    $this->config = uConfig::getInstance();
    $this->initConfigValues();
  }

  protected function getDataSet() {
    $this->initConfigValues();
    $this->resetAutoincrement();
    $dataset = [
      "config" => [
        [
          "map_api" => $this->mapApi,
          "latitude" => $this->latitude,
          "longitude" => $this->longitude,
          "google_key" => $this->googleKey,
          "require_auth" => (int) $this->requireAuth,
          "public_tracks" => (int) $this->publicTracks,
          "pass_lenmin" => $this->passLenMin,
          "pass_strength" => $this->passStrength,
          "interval_seconds" => $this->interval,
          "lang" => $this->lang,
          "units" => $this->units,
          "stroke_weight" => $this->strokeWeight,
          "stroke_color" => hexdec(str_replace('#', '', $this->strokeColor)),
          "stroke_opacity" => $this->strokeOpacity * 100
        ]
      ],
      "ol_layers" => [
        [
          "id" => 1, "name" => $this->testLayer, "url" => $this->testUrl, "priority" => $this->testPriority
        ]
      ]];
    return $this->createArrayDataSet($dataset);
  }

  public function testSetFromDatabase() {
    $this->assertEquals($this->mapApi, $this->config->mapApi);
    $this->assertEquals($this->latitude, $this->config->initLatitude);
    $this->assertEquals($this->longitude, $this->config->initLongitude);
    $this->assertEquals($this->googleKey, $this->config->googleKey);
    $this->assertEquals($this->requireAuth, $this->config->requireAuthentication);
    $this->assertEquals($this->publicTracks, $this->config->publicTracks);
    $this->assertEquals($this->passLenMin, $this->config->passLenMin);
    $this->assertEquals($this->passStrength, $this->config->passStrength);
    $this->assertEquals($this->interval, $this->config->interval);
    $this->assertEquals($this->lang, $this->config->lang);
    $this->assertEquals($this->units, $this->config->units);
    $this->assertEquals($this->strokeWeight, $this->config->strokeWeight);
    $this->assertEquals($this->strokeColor, $this->config->strokeColor);
    $this->assertEquals($this->strokeOpacity, $this->config->strokeOpacity);

    $this->assertEquals($this->testLayer, $this->config->olLayers[0]->name);
    $this->assertEquals($this->testUrl, $this->config->olLayers[0]->url);
    $this->assertEquals($this->testPriority, $this->config->olLayers[0]->priority);
  }

  public function testSave() {
    $this->config->mapApi = 'newApi';
    $this->config->initLatitude = 33.11;
    $this->config->initLongitude = 22.11;
    $this->config->googleKey = 'newKey';
    $this->config->requireAuthentication = false;
    $this->config->publicTracks = false;
    $this->config->passLenMin = 31;
    $this->config->passStrength = 31;
    $this->config->interval = 661;
    $this->config->lang = 'newLang';
    $this->config->units = 'newUnits';
    $this->config->strokeWeight = 551;
    $this->config->strokeColor = '#bfbfbf';
    $this->config->strokeOpacity = 0.11;
    $this->config->olLayers = [];
    $this->config->olLayers[0] = new uLayer(11, 'newLayer', 'newUrl', 51);

    $this->config->save();

    $this->assertEquals(1, $this->getConnection()->getRowCount('config'), "Wrong row count");
    $expected = [
      "map_api" => $this->config->mapApi,
      "latitude" => $this->config->initLatitude,
      "longitude" => $this->config->initLongitude,
      "google_key" => $this->config->googleKey,
      "require_auth" => $this->config->requireAuthentication,
      "public_tracks" => $this->config->publicTracks,
      "pass_lenmin" => $this->config->passLenMin,
      "pass_strength" => $this->config->passStrength,
      "interval_seconds" => $this->config->interval,
      "lang" => $this->config->lang,
      "units" => $this->config->units,
      "stroke_weight" => $this->config->strokeWeight,
      "stroke_color" => hexdec(str_replace('#', '', $this->config->strokeColor)),
      "stroke_opacity" => (int) ($this->config->strokeOpacity * 100)
    ];
    $actual = $this->getConnection()->createQueryTable("config", "SELECT * FROM config");
    $this->assertTableContains($expected, $actual, "Wrong actual table data: " . implode(', ', $actual->getRow(0)));

    $this->assertEquals(1, $this->getConnection()->getRowCount('ol_layers'), "Wrong row count");
    $expected = [
      "id" => $this->config->olLayers[0]->id,
      "name" => $this->config->olLayers[0]->name,
      "url" => $this->config->olLayers[0]->url,
      "priority" => $this->config->olLayers[0]->priority
    ];
    $actual = $this->getConnection()->createQueryTable("ol_layers", "SELECT * FROM ol_layers");
    $this->assertTableContains($expected, $actual, "Wrong actual table data: " . implode(', ', $actual->getRow(0)));
  }

  private function initConfigValues() {
    $this->mapApi = 'testApi';
    $this->latitude = 33.33;
    $this->longitude = 22.22;
    $this->googleKey = 'testKey';
    $this->requireAuth = true;
    $this->publicTracks = true;
    $this->passLenMin = 3;
    $this->passStrength = 3;
    $this->interval = 66;
    $this->lang = 'pl';
    $this->units = 'nautical';
    $this->strokeWeight = 55;
    $this->strokeColor = '#afafaf';
    $this->strokeOpacity = 0.44;
    $this->testLayer = 'testLayer';
    $this->testUrl = 'testUrl';
    $this->testPriority = 5;
  }

  public function testPassRegex() {
    $this->config->passLenMin = 0;
    $this->config->passStrength = 0;
    $password0 = "password";
    $password1 = "PASSword";
    $password2 = "PASSword1234";
    $password3 = "PASSword1234-;";

    $regex = $this->config->passRegex();
    $this->assertRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $this->config->passStrength = 1;
    $regex = $this->config->passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $this->config->passStrength = 2;
    $regex = $this->config->passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $this->config->passStrength = 3;
    $regex = $this->config->passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertNotRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $password_len5 = "12345";
    $password_len10 = "1234567890";
    $this->config->passLenMin = 5;
    $this->config->passStrength = 0;
    $regex = $this->config->passRegex();
    $this->assertRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    $this->config->passLenMin = 7;
    $regex = $this->config->passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    $this->config->passLenMin = 12;
    $regex = $this->config->passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertNotRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");
  }
}
?>
