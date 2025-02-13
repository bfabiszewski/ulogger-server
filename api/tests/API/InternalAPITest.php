<?php
declare(strict_types = 1);

namespace uLogger\Tests\API;

use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use uLogger\Exception\DatabaseException;
use uLogger\Tests\Mapper\Fixtures\UsersOnlyAdmin;


class InternalAPITest extends AbstractAPITestCase {

  /**
   * @throws DatabaseException
   */
  public function setUp(): void {
    parent::setUp();
    $this->insertFixtures([ UsersOnlyAdmin::class ]);
  }

  /*****************************************************************************
   * GET /api/tracks/{id}/positions
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAdmin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $json = $this->performGetRequestSuccess("/api/tracks/$trackId/positions");

    $this->assertCount(2, $json, 'Wrong count of positions');

    $position = $json[0];
    $this->assertEquals(1, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_ADMIN_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');

    $position = $json[1];
    $this->assertEquals(2, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 1, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_ADMIN_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsUser(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition($userId, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $json = $this->performGetRequestSuccess("/api/tracks/$trackId/positions");
    $this->assertCount(2, $json, 'Wrong count of positions');

    $position = $json[0];
    $this->assertEquals(1, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');

    $position = $json[1];
    $this->assertEquals(2, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 1, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsOtherUser(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $userId2 = $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertEquals(2, $userId);
    $this->assertTableRowCount(3, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $trackId = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition($userId2, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $this->performRequestUnauthorized("/api/tracks/$trackId/positions", 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsOtherUserByAdmin(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertEquals(2, $userId);
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition($userId, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $json = $this->performGetRequestSuccess("/api/tracks/$trackId/positions");
    $this->assertCount(2, $json, 'Wrong count of positions');

    $position = $json[0];
    $this->assertEquals(1, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');

    $position = $json[1];
    $this->assertEquals(2, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 1, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsWrongTrackId(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $json = $this->performGetRequestSuccess('/api/tracks/111/positions');
    $this->assertEmpty($json, 'JSON object is not empty');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:0;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->assertTableRowCount(1, 'tracks');

    $json = $this->performGetRequestSuccess("/api/tracks/$trackId/positions");
    $this->assertCount(1, $json, 'Wrong count of positions');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsRequireAuthNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->assertTableRowCount(1, 'tracks');

    $this->performRequestUnauthorized("/api/tracks/$trackId/positions", 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsPublicTracksNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:1;');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->assertTableRowCount(1, 'tracks');

    $this->performRequestUnauthorized("/api/tracks/$trackId/positions", 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAfterId(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $afterId = $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 1, self::TEST_LATITUDE + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $json = $this->performGetRequestSuccess("/api/tracks/$trackId/positions?afterId=$afterId");
    $this->assertCount(1, $json, 'Wrong count of positions');

    $position = $json[0];
    $this->assertEquals($afterId + 1, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE + 1, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 1, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_ADMIN_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
    $this->assertEquals(111195, (int) $position->meters, 'Wrong distance delta');
    $this->assertEquals(1, (int) $position->seconds, 'Wrong timestamp delta');
  }

  /*****************************************************************************
   * GET /api/users/{id}/position
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsUserLatest(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 3);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2, self::TEST_TIMESTAMP + 2);
    $this->addTestPosition($userId, $trackId2, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(2, 'tracks');
    $this->assertTableRowCount(4, 'positions');

    $json = $this->performGetRequestSuccess('/api/users/' . self::TEST_ADMIN_ID . '/position');
    $this->assertInstanceOf(stdClass::class, $json, 'Wrong type of object');

    $position = $json;
    $this->assertEquals(2, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 3, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_ADMIN_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsWrongUserId(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $this->performRequestNotFound('/api/users/111/position', 'get', null);
  }

  /*****************************************************************************
   * GET /api/users/position
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testGetPositionsAllUsersLatest(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP);
    $this->addTestPosition(self::TEST_ADMIN_ID, $trackId, self::TEST_TIMESTAMP + 3);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(2, 'positions');

    $trackName = 'Track 2';
    $trackId2 = $this->addTestTrack($userId, $trackName);
    $this->addTestPosition($userId, $trackId2, self::TEST_TIMESTAMP + 2);
    $this->addTestPosition($userId, $trackId2, self::TEST_TIMESTAMP + 1);
    $this->assertTableRowCount(2, 'tracks');
    $this->assertTableRowCount(4, 'positions');

    $json = $this->performGetRequestSuccess('/api/users/position');
    $this->assertCount(2, $json, 'Wrong count of positions');

    $position = $json[0];
    $this->assertEquals(3, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 2, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals($trackName, (string) $position->trackName, 'Wrong trackname');

    $position = $json[1];
    $this->assertEquals(2, (int) $position->id, 'Wrong position id');
    $this->assertEquals(self::TEST_LATITUDE, (float) $position->latitude, 'Wrong latitude');
    $this->assertEquals(self::TEST_LONGITUDE, (float) $position->longitude, 'Wrong longitude');
    $this->assertEquals(self::TEST_TIMESTAMP + 3, (int) $position->timestamp, 'Wrong timestamp');
    $this->assertEquals(self::TEST_ADMIN_USER, (string) $position->userName, 'Wrong username');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $position->trackName, 'Wrong trackname');
  }

  /*****************************************************************************
   * GET /api/users
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testGetUsersNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:0;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');

    $json = $this->performGetRequestSuccess('/api/users');
    $this->assertCount(3, $json, 'Wrong count of users');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetUsersRequireAuthNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');

    $this->performRequestUnauthorized('/api/users', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetUsersPublicTracksNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:1;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');

    $this->performRequestUnauthorized('/api/users', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetUsersAdmin(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $json = $this->performGetRequestSuccess('/api/users');
    $this->assertCount(3, $json, 'Wrong count of users');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetUsersUser(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $this->performRequestUnauthorized('/api/users', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetUsersPublicTracksUser(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:1;');

    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(3, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $json = $this->performGetRequestSuccess('/api/users');
    $this->assertCount(3, $json, 'Wrong count of users');
  }

  /*****************************************************************************
   * GET /api/users/{id}/tracks
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testGetTracksAdmin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');

    $this->assertTableRowCount(2, 'tracks');

    $json = $this->performGetRequestSuccess('/api/users/' . self::TEST_ADMIN_ID . '/tracks');
    $this->assertCount(2, $json, 'Wrong count of tracks');

    $track = $json[0];
    $this->assertEquals(self::TEST_TRACK2_ID, (int) $track->id, 'Wrong track id');
    $this->assertEquals(self::TEST_TRACK_NAME . '2', (string) $track->name, 'Wrong track name');

    $track = $json[1];
    $this->assertEquals(self::TEST_TRACK_ID, (int) $track->id, 'Wrong track id');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $track->name, 'Wrong track name');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksUser(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $this->addTestTrack($userId);
    $this->addTestTrack($userId, self::TEST_TRACK_NAME . '2');

    $this->assertTableRowCount(2, 'tracks');

    $json = $this->performGetRequestSuccess("/api/users/$userId/tracks");
    $this->assertCount(2, $json, 'Wrong count of tracks');

    $track = $json[0];
    $this->assertEquals(self::TEST_TRACK2_ID, (int) $track->id, 'Wrong track id');
    $this->assertEquals(self::TEST_TRACK_NAME . '2', (string) $track->name, 'Wrong track name');

    $track = $json[1];
    $this->assertEquals(self::TEST_TRACK_ID, (int) $track->id, 'Wrong track id');
    $this->assertEquals(self::TEST_TRACK_NAME, (string) $track->name, 'Wrong track name');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksOtherUser(): void {
    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');

    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestUnauthorized('/api/users/' . self::TEST_ADMIN_ID . '/tracks', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksWrongUserId(): void {
    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');

    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestUnauthorized('/api/users/111/tracks', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:0;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');
    $this->assertTableRowCount(2, 'tracks');

    $json = $this->performGetRequestSuccess('/api/users/' . self::TEST_ADMIN_ID . '/tracks');
    $this->assertCount(2, $json, 'Wrong count of tracks');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksRequireAuthNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:0;');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');
    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestUnauthorized('/api/users/' . self::TEST_ADMIN_ID . '/tracks', 'get');
  }

  /**
   * @throws GuzzleException
   */
  public function testGetTracksPublicTracksNoAuth(): void {
    $this->addTestConfigValue('require_auth', 'b:1;');
    $this->addTestConfigValue('public_tracks', 'b:1;');

    $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->addTestTrack(self::TEST_ADMIN_ID, self::TEST_TRACK_NAME . '2');
    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestUnauthorized('/api/users/' . self::TEST_ADMIN_ID . '/tracks', 'get');
  }

  /*****************************************************************************
   * PUT /api/users/{id}/password
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testChangePassNoAuth(): void {
    $this->addTestConfigValue('pass_strength', 'i:0;');

    $payload = [
      'password' => self::TEST_PASS,
      'oldPassword' => self::TEST_PASS
    ];
    $this->performRequestUnauthorized('/api/users/' . self::TEST_ADMIN_ID . '/password', 'put', $payload);
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassEmpty(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $json = $this->performRequestUnprocessable('/api/users/' . self::TEST_ADMIN_ID . '/password', 'put', null);
    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals("Missing parameter 'password' type 'string'", (string) $json->message, 'Wrong error message');
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassUserUnknown(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $payload = [
      'password' => self::TEST_PASS,
      'oldPassword' => self::TEST_PASS
    ];
    $this->performRequestUnauthorized('/api/users/' . self::TEST_USER2_ID . '/password', 'put', $payload);
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassEmptyLogin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $payload = [
      'password' => self::TEST_PASS,
      'oldPassword' => self::TEST_PASS
    ];
    $this->performRequestNotFound('/api/users//password', 'put', $payload);
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassWrongOldPassword(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $payload = [
      'oldPassword' => 'badpass',
      'password' => 'Newpass1234567890',
    ];
    $json = $this->performRequestUnprocessable('/api/users/' . self::TEST_ADMIN_ID . '/password', 'put', $payload);
    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals('oldpassinvalid', (string) $json->message, 'Wrong error message');
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassNoOldPassword(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $payload = [
      'password' => 'Newpass1234567890',
    ];
    $json = $this->performRequestUnprocessable('/api/users/' . self::TEST_ADMIN_ID . '/password', 'put', $payload);
    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals("Missing parameter 'oldPassword' type 'string'", (string) $json->message, 'Wrong error message');
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassSelfAdmin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $newPass = 'Newpass1234567890';

    $payload = [
      'oldPassword' => self::TEST_ADMIN_PASS,
      'password' => $newPass
    ];
    $this->performRequestNoContent('/api/users/' . self::TEST_ADMIN_ID . '/password', 'put', $payload);
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn('SELECT password FROM users')), 'Wrong actual password hash');
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassSelfUser(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $newPass = 'Newpass1234567890!';

    $payload = [
      'oldPassword' => self::TEST_PASS,
      'password' => $newPass,
    ];
    $this->performRequestNoContent("/api/users/$userId/password", 'put', $payload);
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), 'Wrong actual password hash');
  }

  /**
   * @throws GuzzleException
   */
  public function testChangePassOtherUser(): void {
    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $user2Id = $this->addTestUser(self::TEST_USER2, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $newPass = 'Newpass1234567890';

    $payload = [
      'oldPassword' => self::TEST_PASS,
      'password' => $newPass,
    ];
    $this->performRequestUnauthorized("/api/users/$user2Id/password", 'put', $payload);
  }

  /*****************************************************************************
   * DELETE /api/tracks/{id}
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testDeleteTrackAdmin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestNoContent("/api/tracks/$trackId", 'delete', null);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowExists('tracks', $trackId2);
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteTrackSelf(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertTableRowCount(2, 'tracks');

    $this->performRequestNoContent("/api/tracks/$trackId", 'delete', null);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowExists('tracks', $trackId2);
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteTrackOtherUser(): void {
    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);

    $this->assertTableRowCount(1, 'tracks');

    $this->performRequestUnauthorized("/api/tracks/$trackId", 'delete');
    $this->assertTableRowCount(1, 'tracks');
  }

  /*****************************************************************************
   * PUT /api/tracks/{id}
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testUpdateTrackSuccess(): void {
    $newName = 'New name';
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $trackId = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);

    $this->assertTableRowCount(2, 'tracks');

    $payload = [ 'id' => $trackId, 'userId' => $userId, 'name' => $newName, 'comment' => self::TEST_TRACK_COMMENT ];
    $this->performRequestNoContent("/api/tracks/$trackId", 'put', $payload);
    $this->assertTableRowCount(2, 'tracks');
    $row1 = [
      'id' => $trackId2,
      'user_id' => $userId,
      'name' => self::TEST_TRACK_NAME,
      'comment' => self::TEST_TRACK_COMMENT
    ];
    $row2 = [
      'id' => $trackId,
      'user_id' => $userId,
      'name' => $newName,
      'comment' => self::TEST_TRACK_COMMENT
    ];
    $this->assertTableRow($row1, 'tracks', $trackId2);
    $this->assertTableRow($row2, 'tracks', $trackId);
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateTrackEmptyName(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $trackId = $this->addTestTrack($userId);
    $this->addTestTrack($userId);

    $this->assertTableRowCount(2, 'tracks');

    $payload = [ 'id' => $trackId, 'userId' => $userId, 'name' => '', 'comment' => self::TEST_TRACK_COMMENT ];
    $json = $this->performRequestUnprocessable("/api/tracks/$trackId", 'put', $payload);
    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals('Malformed track object', (string) $json->message, 'Wrong error message');
    $this->assertTableRowCount(2, 'tracks');
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateTrackNonexistentTrack(): void {
    $newName = 'New name';
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $trackId = $this->addTestTrack($userId);
    $nonexistentTrackId = $trackId + 1;
    $this->assertTableRowCount(1, 'tracks');
    $this->assertFalse($this->pdoGetColumn("SELECT id FROM tracks WHERE id = $nonexistentTrackId"), 'Nonexistant track exists');

    $payload = [ 'id' => $nonexistentTrackId, 'userId' => $userId, 'name' => $newName ];
    $json = $this->performRequestUnprocessable("/api/tracks/$trackId", 'put', $payload);

    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals('Wrong track id', (string) $json->message, 'Wrong error message');
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateTrackMissingUserId(): void {
    $newName = 'New name';
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $trackId = $this->addTestTrack($userId);
    $this->assertTableRowCount(1, 'tracks');

    $payload = [ 'id' => $trackId, 'name' => $newName ];
    $json = $this->performRequestUnprocessable("/api/tracks/$trackId", 'put', $payload);

    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals("Missing value for field 'userId'", (string) $json->message, 'Wrong error message');
  }

  /*****************************************************************************
   * DELETE /api/users/{id}
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testDeleteUserNonAdmin(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(self::TEST_USER, self::TEST_PASS), 'Authentication failed');

    $this->performRequestUnauthorized("/api/users/$userId", 'delete');

    $this->assertTableRowCount(2, 'users');
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteUserSelf(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $this->assertTableRowCount(1, 'users');

    $json = $this->performRequestUnprocessable('/api/users/' . self::TEST_ADMIN_ID, 'delete', null);

    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals('selfeditwarn', (string) $json->message, 'Wrong error message');
    $this->assertTableRowCount(1, 'users');
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteUserNoAuth(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $this->performRequestUnauthorized("/api/users/$userId", 'delete');

    $this->assertTableRowCount(2, 'users');
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteUserSuccess(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $this->performRequestNoContent("/api/users/$userId", 'delete', null);

    $this->assertTableRowCount(1, 'users');
  }

  /*****************************************************************************
   * POST /api/users
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testAddUserSuccess(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $this->assertTableRowCount(1, 'users');

    $payload = [ 'login' => self::TEST_USER, 'password' => self::TEST_PASS ];
    $json = $this->performRequestCreated('/api/users', 'post', $payload);

    self::assertSame(self::TEST_USER, $json->login);
    self::assertNull($json->password);
    self::assertSame($json->id, 2);
    self::assertFalse($json->isAdmin);
    $this->assertTableRowCount(2, 'users');
    $this->assertTableRowValue(2, 'users', 2, 'id');
    $this->assertTableRowValue(self::TEST_USER, 'users', 2, 'login');
    $this->assertTableRowValue(0, 'users', 2, 'admin');
    $this->assertTrue(password_verify(self::TEST_PASS, $this->pdoGetColumn("SELECT password FROM users WHERE login = '" . self::TEST_USER . "'")), 'Wrong actual password hash');
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserSameLogin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $payload = [ 'login' => self::TEST_USER, 'password' => self::TEST_PASS ];
    $json = $this->performRequestConflict('/api/users', 'post', $payload);

    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals('userexists', (string) $json->message, 'Wrong error message');
    $this->assertTableRowCount(2, 'users');
  }

  /*****************************************************************************
   * PUT /api/users/{id}
   *****************************************************************************/

  /**
   * @throws GuzzleException
   */
  public function testUpdateUserSuccess(): void {
    $newPass = self::TEST_PASS . 'new';
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $payload = [ 'id' => $userId, 'login' => self::TEST_USER, 'password' => $newPass ];
    $this->performRequestNoContent("/api/users/$userId", 'put', $payload);

    $this->assertTableRowCount(2, 'users');
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE login = '" . self::TEST_USER . "'")), 'Wrong actual password hash');
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateUserEmptyPass(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');

    $payload = [ 'id' => $userId, 'login' => self::TEST_USER, 'password' => '' ];
    $this->performRequestNoContent("/api/users/$userId", 'put', $payload);

    $this->assertTableRowCount(2, 'users');
    // password will not change
    $this->assertTrue(password_verify(self::TEST_PASS, $this->pdoGetColumn("SELECT password FROM users WHERE login = '" . self::TEST_USER . "'")), 'Wrong actual password hash');
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateUserEmptyLogin(): void {
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));
    $this->assertTableRowCount(2, 'users');
    $this->assertTrue($this->authenticate(), 'Authentication failed');

    $payload = [ 'id' => $userId ];
    $json = $this->performRequestUnprocessable("/api/users/$userId", 'put', $payload);

    $this->assertEquals(1, (int) $json->error, 'Wrong error status');
    $this->assertEquals("Missing value for field 'login'", (string) $json->message, 'Wrong error message');
    $this->assertTableRowCount(2, 'users');
  }

  /**
   * @throws GuzzleException
   */
  public function testUpdateUserPassOtherAdmin(): void {
    $this->assertTrue($this->authenticate(), 'Authentication failed');
    $userId = $this->addTestUser(self::TEST_USER, password_hash(self::TEST_PASS, PASSWORD_DEFAULT));

    $newPass = 'Newpass1234567890!';

    $payload = [
      'id' => $userId,
      'login' => self::TEST_USER,
      'password' => $newPass,
      'isAdmin' => false,
    ];
    $this->performRequestNoContent("/api/users/$userId", 'put', $payload);
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = $userId")), 'Wrong actual password hash');
  }
}

?>
