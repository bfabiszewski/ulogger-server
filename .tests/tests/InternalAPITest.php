<?php

use GuzzleHttp\Exception\GuzzleException;

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");
if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");

class InternalAPITest extends UloggerAPITestCase {

  /* getpositions */

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAdmin(): void {

    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals(1, (int) $position->id, "Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testAdminUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");

    $position = $json[1];
    self::assertEquals(2, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 1, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testAdminUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsUser(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals(1, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");

    $position = $json[1];
    self::assertEquals(2, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 1, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsOtherUser(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $userId);
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of positions");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsOtherUserByAdmin(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $userId);
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals(1, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");

    $position = $json[1];
    self::assertEquals(2, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 1, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsUserLatest(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 3);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 2);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 1);
    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(4, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "last" => 1 ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(1, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals(2, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 3, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testAdminUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAllUsersLatest(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 3);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $trackName = "Track 2";
    $trackId2 = $this->addTestTrack($userId, $trackName);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 2);
    $this->addTestPosition($userId, $trackId2, $this->testTimestamp + 1);
    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(4, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "last" => 1 ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals(2, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 3, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testAdminUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");

    $position = $json[1];
    self::assertEquals(3, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 2, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testUser, (string) $position->username,"Wrong username");
    self::assertEquals($trackName, (string) $position->trackname,"Wrong trackname");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsNoTrackId(): void {

    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of positions");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsNoUserId(): void {

    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of positions");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsNoAuth(): void {

    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of positions");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAfterId(): void {

    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $afterId = $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1, $this->testLat + 1);
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId, "trackid" => $trackId, "afterid" => $afterId ],
    ];
    $response = $this->http->get("/utils/getpositions.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    self::assertNotNull($json, "JSON object is null");
    self::assertCount(1, $json, "Wrong count of positions");

    $position = $json[0];
    self::assertEquals($afterId + 1, (int) $position->id,"Wrong position id");
    self::assertEquals($this->testLat + 1, (float) $position->latitude,"Wrong latitude");
    self::assertEquals($this->testLon, (float) $position->longitude,"Wrong longitude");
    self::assertEquals($this->testTimestamp + 1, (int) $position->timestamp,"Wrong timestamp");
    self::assertEquals($this->testAdminUser, (string) $position->username,"Wrong username");
    self::assertEquals($this->testTrackName, (string) $position->trackname,"Wrong trackname");
    self::assertEquals(111195, (int) $position->meters,"Wrong distance delta");
    self::assertEquals(1, (int) $position->seconds,"Wrong timestamp delta");
  }


  /* gettracks.php */


  /**
   * @throws GuzzleException
   */
  public function testGetTracksAdmin(): void {

    self::assertTrue($this->authenticate(), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of tracks");

    $track = $json[0];
    self::assertEquals($this->testTrackId2, (int) $track->id,"Wrong track id");
    self::assertEquals($this->testTrackName . "2", (string) $track->name,"Wrong track name");

    $track = $json[1];
    self::assertEquals($this->testTrackId, (int) $track->id,"Wrong track id");
    self::assertEquals($this->testTrackName, (string) $track->name,"Wrong track name");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksUser(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($userId);
    $this->addTestTrack($userId, $this->testTrackName . "2");

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $userId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(2, $json, "Wrong count of tracks");

    $track = $json[0];
    self::assertEquals($this->testTrackId2, (int) $track->id,"Wrong track id");
    self::assertEquals($this->testTrackName . "2", (string) $track->name,"Wrong track name");

    $track = $json[1];
    self::assertEquals($this->testTrackId, (int) $track->id,"Wrong track id");
    self::assertEquals($this->testTrackName, (string) $track->name,"Wrong track name");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksOtherUser(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of tracks");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksNoUserId(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of tracks");
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksNoAuth(): void {

    $this->addTestTrack($this->testUserId);
    $this->addTestTrack($this->testUserId, $this->testTrackName . "2");

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "query" => [ "userid" => $this->testUserId ],
    ];
    $response = $this->http->get("/utils/gettracks.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertCount(0, $json, "Wrong count of tracks");
  }


  /* changepass.php */

  /**
   * @throws GuzzleException
   */
  public function testChangePassNoAuth(): void {

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "pass" => $this->testPass,
        "oldpass" => $this->testPass
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(401, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("User not authorized", (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassEmpty(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [ "login" => $this->testAdminUser ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("Empty password", (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassUserUnknown(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser,
        "pass" => $this->testPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("User unknown", (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassEmptyLogin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "pass" => $this->testPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("Empty login", (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassWrongOldpass(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testAdminUser,
        "oldpass" => "badpass",
        "pass" => "Newpass1234567890",
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("Wrong old password", (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassNoOldpass(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testAdminUser,
        "pass" => "Newpass1234567890",
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals("Wrong old password", (string) $json->message,"Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassSelfAdmin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

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
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassSelfUser(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

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
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassOtherAdmin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
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
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassOtherUser(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->addTestUser($this->testUser2, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $newPass = "Newpass1234567890";

    $options = [
      "http_errors" => false,
      "form_params" => [
        "login" => $this->testUser2,
        "pass" => $newPass,
      ],
    ];
    $response = $this->http->post("/utils/changepass.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals("User not authorized", (string) $json->message, "Wrong error message");
  }

  /* handletrack.php */

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackDeleteAdmin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals($trackId2, $this->pdoGetColumn("SELECT id FROM tracks WHERE id = $trackId2"), "Wrong actual track id");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackDeleteSelf(): void {
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertEquals($trackId2, $this->pdoGetColumn("SELECT id FROM tracks WHERE id = $trackId2"), "Wrong actual track id");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackDeleteOtherUser(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);

    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "delete" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals($lang["notauthorized"], (string) $json->message, "Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackUpdate(): void {
    $newName = "New name";
    self::assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "update", "trackname" => $newName ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
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

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackUpdateEmptyName(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $this->addTestTrack($userId);

    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $trackId, "action" => "update", "trackname" => "" ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
    self::assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackUpdateNonexistantTrack(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $newName = "New name";
    self::assertTrue($this->authenticate(), "Authentication failed");
    $userId = $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $trackId = $this->addTestTrack($userId);
    $nonexistantTrackId = $trackId + 1;
    self::assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    self::assertFalse($this->pdoGetColumn("SELECT id FROM tracks WHERE id = $nonexistantTrackId"), "Nonexistant track exists");

    $options = [
      "http_errors" => false,
      "form_params" => [ "trackid" => $nonexistantTrackId, "action" => "update", "trackname" => $newName ],
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleTrackMissingAction(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->post("/utils/handletrack.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
  }


  /* handleuser.php */

  /**
   * @throws GuzzleException
   */
  public function testHandleUserMissingAction(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");

    $options = [
      "http_errors" => false,
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserNonAdmin(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => "test" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserSelf(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testAdminUser ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserEmptyLogin(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => "" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserNoAuth(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testUser ],

    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error,"Wrong error status");
    self::assertEquals($lang["servererror"], (string) $json->message,"Wrong error message");
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserAdd(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "add", "login" => $this->testUser, "pass" => $this->testPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $expected = [
      "login" => $this->testUser,
    ];
    $actual = $this->getConnection()->createQueryTable(
      "users",
      "SELECT login FROM users"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    self::assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserAddSameLogin(): void {
    $lang = (new uLang($this->mockConfig))->getStrings();
    self::assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "add", "login" => $this->testUser, "pass" => $this->testPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, (int) $json->error, "Wrong error status");
    self::assertEquals($lang["userexists"], (string) $json->message,"Wrong error message");
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserUpdate(): void {
    $newPass = $this->testPass . "new";
    self::assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "update", "login" => $this->testUser, "pass" => $newPass ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserUpdateEmptyPass(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "update", "login" => $this->testUser, "pass" => "" ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    self::assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '$this->testUser'")), "Wrong actual password hash");
  }

  /**
   * @throws GuzzleException
   */
  public function testHandleUserDelete(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");

    $options = [
      "http_errors" => false,
      "form_params" => [ "action" => "delete", "login" => $this->testUser ],
    ];
    $response = $this->http->post("/utils/handleuser.php", $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode($response->getBody());
    self::assertNotNull($json, "JSON object is null");
    self::assertEquals(1, $this->getConnection()->getRowCount("users"), "Wrong row count");
  }

}

?>