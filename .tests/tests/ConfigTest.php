<?php

if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(__DIR__ . "/../../helpers/config.php");
require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");

class ConfigTest extends UloggerDatabaseTestCase {
  
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
    uConfig::setFromDatabase();
    $this->assertEquals($this->mapApi, uConfig::$mapApi);
    $this->assertEquals($this->latitude, uConfig::$initLatitude);
    $this->assertEquals($this->longitude, uConfig::$initLongitude);
    $this->assertEquals($this->googleKey, uConfig::$googleKey);
    $this->assertEquals($this->requireAuth, uConfig::$requireAuthentication);
    $this->assertEquals($this->publicTracks, uConfig::$publicTracks);
    $this->assertEquals($this->passLenMin, uConfig::$passLenMin);
    $this->assertEquals($this->passStrength, uConfig::$passStrength);
    $this->assertEquals($this->interval, uConfig::$interval);
    $this->assertEquals($this->lang, uConfig::$lang);
    $this->assertEquals($this->units, uConfig::$units);
    $this->assertEquals($this->strokeWeight, uConfig::$strokeWeight);
    $this->assertEquals($this->strokeColor, uConfig::$strokeColor);
    $this->assertEquals($this->strokeOpacity, uConfig::$strokeOpacity);

    $this->assertEquals($this->testLayer, uConfig::$olLayers[0]->name);
    $this->assertEquals($this->testUrl, uConfig::$olLayers[0]->url);
    $this->assertEquals($this->testPriority, uConfig::$olLayers[0]->priority);
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
    uConfig::$passLenMin = 0;
    uConfig::$passStrength = 0;
    $password0 = "password";
    $password1 = "PASSword";
    $password2 = "PASSword1234";
    $password3 = "PASSword1234-;";

    $regex = uConfig::passRegex();
    $this->assertRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$passStrength = 1;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$passStrength = 2;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$passStrength = 3;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertNotRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $password_len5 = "12345";
    $password_len10 = "1234567890";
    uConfig::$passLenMin = 5;
    uConfig::$passStrength = 0;
    $regex = uConfig::passRegex();
    $this->assertRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    uConfig::$passLenMin = 7;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    uConfig::$passLenMin = 12;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertNotRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");
  }
}
?>
