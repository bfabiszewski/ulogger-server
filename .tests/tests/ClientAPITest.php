<?php

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");

class ClientAPITest extends UloggerAPITestCase {

  public function testNoAction() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $options = [
      'http_errors' => false
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
  }

  /* auth */

  public function testAuthOk() {
    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'auth', 'user' => $this->testAdminUser, 'pass' => $this->testAdminPass ],
    ];

    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertFalse($json->{'error'}, "Unexpected error");
  }

  public function testAuthFail() {
    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'user' => 'noexist', 'pass' => 'noexist' ],
    ];

    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(401, $response->getStatusCode(), "Unexpected status code");
  }

  /* adduser */

  public function testAddUser() {
    $this->assertTrue($this->authenticate(), "Authentication failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertFalse($json->{'error'}, "Unexpected error");
    $this->assertEquals(2, $json->{'userid'}, "Wrong user id");
    $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $expected = [ "id" => 2, "login" => $this->testUser ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login FROM users");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testAddUserExistingLogin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testAdminUser, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'userid'}), "Unexpected user id");
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  public function testAddUserEmptyLogin() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => '', 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'userid'}), "Unexpected user id");
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  public function testAddUserEmptyPass() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser, 'password' => '' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'userid'}), "Unexpected user id");
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  public function testAddUserNoParameters() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'userid'}), "Unexpected user id");
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  public function testAddUserByNonAdmin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertTrue($this->authenticate($this->testUser, $this->testPass), "Authentication failed");

    $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'adduser', 'login' => $this->testUser2, 'password' => $this->testPass ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'userid'}), "Unexpected user id");
    $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");
  }

  /* addtrack */

  public function testAddTrack() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack', 'track' => $this->testTrackName ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertFalse($json->{'error'}, "Unexpected error");
    $this->assertEquals(1, $json->{'trackid'}, "Wrong track id");
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $expected = [ "id" => 1, "user_id" => 1, "name" => $this->testTrackName ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, user_id, name FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testAddTrackEmptyName() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack', 'track' => '' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'trackid'}), "Unexpected track id");
    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
  }

  public function testAddTrackNoParameters() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'addtrack' ],
    ];
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertFalse(isset($json->{'trackid'}), "Unexpected track id");
    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
  }

  /* addpos */

  public function testAddPosition() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

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
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertFalse($json->{'error'}, "Unexpected error");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
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

  public function testAddPositionWithImage() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

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
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertFalse($json->{'error'}, "Unexpected error");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
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
    $this->assertEquals($expected['id'], $actual->getValue(0, 'id'));
    $this->assertEquals($expected['user_id'], $actual->getValue(0, 'user_id'));
    $this->assertEquals($expected['track_id'], $actual->getValue(0, 'track_id'));
    $this->assertEquals($expected['time'], $actual->getValue(0, 'time'));
    $this->assertEquals($expected['latitude'], $actual->getValue(0, 'latitude'));
    $this->assertEquals($expected['longitude'], $actual->getValue(0, 'longitude'));
    $this->assertEquals($expected['altitude'], $actual->getValue(0, 'altitude'));
    $this->assertEquals($expected['speed'], $actual->getValue(0, 'speed'));
    $this->assertEquals($expected['bearing'], $actual->getValue(0, 'bearing'));
    $this->assertEquals($expected['accuracy'], $actual->getValue(0, 'accuracy'));
    $this->assertEquals($expected['provider'], $actual->getValue(0, 'provider'));
    $this->assertEquals($expected['comment'], $actual->getValue(0, 'comment'));
    $this->assertContains('.jpg', $actual->getValue(0, 'image'));
  }

  public function testAddPositionNoexistantTrack() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

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
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $json = json_decode((string) $response->getBody());
    $this->assertTrue($json->{'error'}, "Unexpected success");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testAddPositionEmptyParameters() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

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
      $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      $this->assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testAddPositionMissingParameters() {
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

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
      $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      $this->assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    // optional
    $optional = [ 'altitude', 'speed', 'bearing', 'accuracy', 'provider', 'comment', 'imageid' ];
    foreach ($optional as $parameter) {
      $optCopy = $options;
      unset($optCopy['form_params'][$parameter]);
      $response = $this->http->post('/client/index.php', $optCopy);
      $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
      $json = json_decode((string) $response->getBody());
      $this->assertFalse($json->{'error'}, "Unexpected error ($parameter)");
    }
    $this->assertEquals(count($optional), $this->getConnection()->getRowCount('positions'), "Wrong row count");

  }


}

?>
