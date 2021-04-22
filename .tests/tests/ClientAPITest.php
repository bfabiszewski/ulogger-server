<?php

use GuzzleHttp\Exception\GuzzleException;

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");

class ClientAPITest extends UloggerAPITestCase {

  /**
   * @throws GuzzleException
   */
  public function testNoAction(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    $options = [
      'http_errors' => false
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
  }

  /* auth */

  /**
   * @throws GuzzleException
   */
  public function testAuthOk(): void {
    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'auth', 'user' => $this->testAdminUser, 'pass' => $this->testAdminPass ],
    ];

    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertFalse($json->{'error'}, "Unexpected error");
  }

  /**
   * @throws GuzzleException
   */
  public function testAuthFail(): void {
    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'user' => 'noexist', 'pass' => 'noexist' ],
    ];

    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(401, $response->getStatusCode(), "Unexpected status code");
  }

  /* adduser */
  /**
   * @throws GuzzleException
   */
  public function testAddUser(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertFalse($json->{'error'}, "Unexpected error");
    self::assertEquals(2, $json->{'userid'}, "Wrong user id");
    self::assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $expected = [ "id" => 2, "login" => $this->testUser ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login FROM users");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserExistingLogin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testAdminUser, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'userid'}), "Unexpected user id");
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserEmptyLogin(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => '', 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'userid'}), "Unexpected user id");
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserEmptyPass(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser, 'password' => '' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'userid'}), "Unexpected user id");
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserNoParameters(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'userid'}), "Unexpected user id");
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddUserByNonAdmin(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    self::assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser2, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'userid'}), "Unexpected user id");
    self::assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /* addtrack */
  /**
   * @throws GuzzleException
   */
  public function testAddTrack(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack', 'track' => $this->testTrackName ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertFalse($json->{'error'}, "Unexpected error");
    self::assertEquals(1, $json->{'trackid'}, "Wrong track id");
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $expected = [ "id" => 1, "user_id" => 1, "name" => $this->testTrackName ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, user_id, name FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddTrackEmptyName(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack', 'track' => '' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'trackid'}), "Unexpected track id");
    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddTrackNoParameters(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertFalse(isset($json->{'trackid'}), "Unexpected track id");
    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
  }

  /* addpos */
  /**
   * @throws GuzzleException
   */
  public function testAddPosition(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $trackId,
        'time' => $this->testTimestamp,
        'lat' => $this->testLat,
        'lon' => $this->testLon,
        'altitude' => $this->testAltitude,
        'speed' => $this->testSpeed,
        'bearing' => $this->testBearing,
        'accuracy' => $this->testAccuracy,
        'provider' => $this->testProvider,
        'comment' => $this->testComment
      ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertFalse($json->{'error'}, "Unexpected error");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $expected = [
      "id" => 1,
      "user_id" => $this->testUserId,
      "track_id" => $trackId,
      "time" => $this->testTimestamp,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => $this->testAltitude,
      "speed" => $this->testSpeed,
      "bearing" => $this->testBearing,
      "accuracy" => $this->testAccuracy,
      "provider" => $this->testProvider,
      "comment" => $this->testComment,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, user_id, track_id, " . $this->unix_timestamp('time') . " AS time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionWithImage(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'multipart' => [
        [
          'name' => 'action',
          'contents' => 'addpos',
        ],
        [
          'name' => 'trackid',
          'contents' => $trackId,
        ],
        [
          'name' => 'time',
          'contents' => $this->testTimestamp,
        ],
        [
          'name' => 'lat',
          'contents' => $this->testLat,
        ],
        [
          'name' => 'lon',
          'contents' => $this->testLon,
        ],
        [
          'name' => 'altitude',
          'contents' => $this->testAltitude,
        ],
        [
          'name' => 'speed',
          'contents' => $this->testSpeed,
        ],
        [
          'name' => 'bearing',
          'contents' => $this->testBearing,
        ],
        [
          'name' => 'accuracy',
          'contents' => $this->testAccuracy,
        ],
        [
          'name' => 'provider',
          'contents' => $this->testProvider,
        ],
        [
          'name' => 'comment',
          'contents' => $this->testComment,
        ],
        [
          'name' => 'image',
          'contents' => 'DEADBEEF',
          'filename' => 'upload',
          'headers' => [ 'Content-Type' => 'image/jpeg', 'Content-Transfer-Encoding' => 'binary' ]
        ]
      ]
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertFalse($json->{'error'}, "Unexpected error");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $expected = [
      "id" => 1,
      "user_id" => $this->testUserId,
      "track_id" => $trackId,
      "time" => $this->testTimestamp,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => $this->testAltitude,
      "speed" => $this->testSpeed,
      "bearing" => $this->testBearing,
      "accuracy" => $this->testAccuracy,
      "provider" => $this->testProvider,
      "comment" => $this->testComment
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, user_id, track_id, " . $this->unix_timestamp('time') . " AS time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    self::assertEquals($expected['id'], $actual->getValue(0, 'id'));
    self::assertEquals($expected['user_id'], $actual->getValue(0, 'user_id'));
    self::assertEquals($expected['track_id'], $actual->getValue(0, 'track_id'));
    self::assertEquals($expected['time'], $actual->getValue(0, 'time'));
    self::assertEquals($expected['latitude'], $actual->getValue(0, 'latitude'));
    self::assertEquals($expected['longitude'], $actual->getValue(0, 'longitude'));
    self::assertEquals($expected['altitude'], $actual->getValue(0, 'altitude'));
    self::assertEquals($expected['speed'], $actual->getValue(0, 'speed'));
    self::assertEquals($expected['bearing'], $actual->getValue(0, 'bearing'));
    self::assertEquals($expected['accuracy'], $actual->getValue(0, 'accuracy'));
    self::assertEquals($expected['provider'], $actual->getValue(0, 'provider'));
    self::assertEquals($expected['comment'], $actual->getValue(0, 'comment'));
    self::assertStringContainsString('.jpg', $actual->getValue(0, 'image'));
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionNoexistantTrack(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $this->testTrackId,
        'time' => $this->testTimestamp,
        'lat' => $this->testLat,
        'lon' => $this->testLon,
        'altitude' => $this->testAltitude,
        'speed' => $this->testSpeed,
        'bearing' => $this->testBearing,
        'accuracy' => $this->testAccuracy,
        'provider' => $this->testProvider,
        'comment' => $this->testComment,
        'imageid' => $this->testImage
      ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    self::assertTrue($json->{'error'}, "Unexpected success");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionEmptyParameters(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $trackId,
        'time' => $this->testTimestamp,
        'lat' => $this->testLat,
        'lon' => $this->testLon,
        'altitude' => $this->testAltitude,
        'speed' => $this->testSpeed,
        'bearing' => $this->testBearing,
        'accuracy' => $this->testAccuracy,
        'provider' => $this->testProvider,
        'comment' => $this->testComment,
        'imageid' => $this->testImage
      ],
    ];

    // required
    foreach ([ 'trackid', 'time', 'lat', 'lon' ] as $parameter) {
      $optCopy = $options;
      $optCopy['form_params'][$parameter] = '';
      $response = $this->http->post('/client/index.php', $optCopy);
      self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      self::assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionMissingParameters(): void {
    self::assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $trackId,
        'time' => $this->testTimestamp,
        'lat' => $this->testLat,
        'lon' => $this->testLon,
        'altitude' => $this->testAltitude,
        'speed' => $this->testSpeed,
        'bearing' => $this->testBearing,
        'accuracy' => $this->testAccuracy,
        'provider' => $this->testProvider,
        'comment' => $this->testComment,
        'imageid' => $this->testImage
      ],
    ];

    // required
    foreach ([ 'trackid', 'time', 'lat', 'lon' ] as $parameter) {
      $optCopy = $options;
      unset($optCopy['form_params'][$parameter]);
      $response = $this->http->post('/client/index.php', $optCopy);
      self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      self::assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    // optional
    $optional = [ 'altitude', 'speed', 'bearing', 'accuracy', 'provider', 'comment', 'imageid' ];
    foreach ($optional as $parameter) {
      $optCopy = $options;
      unset($optCopy['form_params'][$parameter]);
      $response = $this->http->post('/client/index.php', $optCopy);
      self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      self::assertFalse($json->{'error'}, "Unexpected error ($parameter)");
    }
    self::assertEquals(count($optional), $this->getConnection()->getRowCount('positions'), "Wrong row count");

  }


}

?>
