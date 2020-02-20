<?php

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");
if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");

class InternalAPITest extends UloggerAPITestCase {

  /* getpositions */

  public function testGetPositionsAdmin() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of positions");

    $position = $json[0];
    $this->assertEquals((int) $position->id, 1, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testAdminUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");

    $position = $json[1];
    $this->assertEquals((int) $position->id, 2, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 1, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testAdminUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");
  }

  public function testGetPositionsUser() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of positions");

    $position = $json[0];
    $this->assertEquals((int) $position->id, 1, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");

    $position = $json[1];
    $this->assertEquals((int) $position->id, 2, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 1, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");
  }

  public function testGetPositionsOtherUser() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $userId);
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 0, "Wrong count of positions");
  }

  public function testGetPositionsOtherUserByAdmin() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $userId);
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of positions");

    $position = $json[0];
    $this->assertEquals((int) $position->id, 1, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");

    $position = $json[1];
    $this->assertEquals((int) $position->id, 2, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 1, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");
  }

  public function testGetPositionsUserLatest() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 3);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 2);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 1);
    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(4, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "last" => 1 ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of positions");

    $position = $json[0];
    $this->assertEquals((int) $position->id, 2, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 3, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testAdminUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");
  }

  public function testGetPositionsAllUsersLatest() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 3);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $trackName = "Track 2";
    $trackId2 = $this->addTestTrack($userId, $trackName);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 2);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 1);
    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(4, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "last" => 1 ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of positions");

    $position = $json[0];
    $this->assertEquals((int) $position->id, 2, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 3, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testAdminUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $this->testTrackName, "Wrong trackname");

    $position = $json[1];
    $this->assertEquals((int) $position->id, 3, "Wrong position id");
    $this->assertEquals((float) $position->latitude, $this->testLat, "Wrong latitude");
    $this->assertEquals((float) $position->longitude, $this->testLon, "Wrong longitude");
    $this->assertEquals((int) $position->timestamp, $this->testTimestamp + 2, "Wrong timestamp");
    $this->assertEquals((string) $position->username, $this->testUser, "Wrong username");
    $this->assertEquals((string) $position->trackname, $trackName, "Wrong trackname");
  }

  public function testGetPositionsNoTrackId() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertCount(0, $json, "Wrong count of positions");
  }

  public function testGetPositionsNoUserId() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertCount(0, $json, "Wrong count of positions");
  }

  public function testGetPositionsNoAuth() {

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 0, "Wrong count of positions");
  }


  /* gettracks.php */


  public function testGetTracksAdmin() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals((int) $track->id, $this->testTrackId2, "Wrong track id");
    $this->assertEquals((string) $track->name, $this->testTrackName . "2", "Wrong track name");

    $track = $json[1];
    $this->assertEquals((int) $track->id, $this->testTrackId, "Wrong track id");
    $this->assertEquals((string) $track->name, $this->testTrackName, "Wrong track name");
  }

  public function testGetTracksUser() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($userId);
    $this->addTestTrack($userId, $this->testTrackName . "2");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals((int) $track->id, $this->testTrackId2, "Wrong track id");
    $this->assertEquals((string) $track->name, $this->testTrackName . "2", "Wrong track name");

    $track = $json[1];
    $this->assertEquals((int) $track->id, $this->testTrackId, "Wrong track id");
    $this->assertEquals((string) $track->name, $this->testTrackName, "Wrong track name");
  }

  public function testGetTracksOtherUser() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 0, "Wrong count of tracks");
  }

  public function testGetTracksNoUserId() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 0, "Wrong count of tracks");
  }

  public function testGetTracksNoAuth() {

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 0, "Wrong count of tracks");
  }


  /* changepass.php */

  public function testChangePassNoAuth() {

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "pass" => $this->testPass,
        "oldpass" => $this->testPass
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(401, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Unauthorized", "Wrong error message");
  }

  public function testChangePassEmpty() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [ "login" => $this->testAdminUser ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Empty password", "Wrong error message");
  }

  public function testChangePassUserUnknown() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "pass" => $this->testPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "User unknown", "Wrong error message");
  }

  public function testChangePassEmptyLogin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "pass" => $this->testPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Empty login", "Wrong error message");
  }

  public function testChangePassWrongOldpass() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testAdminUser,
        "oldpass" => "badpass",
        "pass" => "Newpass1234567890",
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Wrong old password", "Wrong error message");
  }

  public function testChangePassNoOldpass() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testAdminUser,
        "pass" => "Newpass1234567890",
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Wrong old password", "Wrong error message");
  }

  public function testChangePassSelfAdmin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $newPass = "Newpass1234567890";

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testAdminUser,
        "oldpass" => $this->testAdminPass,
        "pass" => $newPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
  }

  public function testChangePassSelfUser() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $newPass = "Newpass1234567890";

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "oldpass" => $this->testPass,
        "pass" => $newPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), "Wrong actual password hash");
  }

  public function testChangePassOtherAdmin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));

    $newPass = "Newpass1234567890";

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "pass" => $newPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), "Wrong actual password hash");
  }

  public function testChangePassOtherUser() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->addTestUser($this->testUser2, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $newPass = "Newpass1234567890";

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser2,
        "pass" => $newPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, "Unauthorized", "Wrong error message");
  }

  /* handletrack.php */

  public function testHandleTrackDeleteAdmin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals($trackId2, $this->pdoGetColumn("SELECT id FROM tracks WHERE id = $trackId2"), "Wrong actual track id");
  }

  public function testHandleTrackDeleteSelf() {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals($trackId2, $this->pdoGetColumn("SELECT id FROM tracks WHERE id = $trackId2"), "Wrong actual track id");
  }

  public function testHandleTrackDeleteOtherUser() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
  }

  public function testHandleTrackUpdate() {
    $newName = "New name";
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "update", "trackname" => $newName ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $row1 = [
      "id" => $trackId2,
      "user_id" => $userId,
      "name" => $this->testTrackName,
      "comment" => $this->testTrackComment
    ];
    $row2 = [
      "id" => $trackId,
      "user_id" => $userId,
      "name" => $newName,
      "comment" => $this->testTrackComment
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($row1, $actual, "Wrong actual table data");
    $this->assertTableContains($row2, $actual, "Wrong actual table data");
  }

  public function testHandleTrackUpdateEmptyName() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $this->addTestTrack($userId);

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "update", "trackname" => "" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
  }

  public function testHandleTrackUpdateNonexistantTrack() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $newName = "New name";
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $nonexistantTrackId = $trackId + 1;
    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertFalse($this->pdoGetColumn("SELECT id FROM tracks WHERE id = $nonexistantTrackId"), "Nonexistant track exists");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $nonexistantTrackId, "action" => "update", "trackname" => $newName ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
  }

  public function testHandleTrackMissingAction() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
  }


  /* handleuser.php */

  public function testHandleUserMissingAction() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
  }

  public function testHandleUserNonAdmin() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => "test" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

  }

  public function testHandleUserSelf() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testAdminUser ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  public function testHandleUserEmptyLogin() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => "" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  public function testHandleUserNoAuth() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testUser ],

    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals((int) $json->error, 1, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["servererror"], "Wrong error message");
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  public function testHandleUserAdd() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "add", "login" => $this->testUser, "pass" => $this->testPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $expected = [
      "login" => $this->testUser,
    ];
    $actual = $this->getConnection()->createQueryTable(
      "users",
      "SELECT login FROM users"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $this->assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  public function testHandleUserAddSameLogin() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "add", "login" => $this->testUser, "pass" => $this->testPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, (int) $json->error, "Wrong error status");
    $this->assertEquals((string) $json->message, $lang["userexists"], "Wrong error message");
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  public function testHandleUserUpdate() {
    $newPass = $this->testPass . "new";
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "update", "login" => $this->testUser, "pass" => $newPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  public function testHandleUserUpdateEmptyPass() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "update", "login" => $this->testUser, "pass" => "" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $this->assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  public function testHandleUserDelete() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testUser ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

}

?>