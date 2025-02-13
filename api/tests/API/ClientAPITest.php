<?php
declare(strict_types = 1);

namespace uLogger\Tests\API;

use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use uLogger\Exception\DatabaseException;
use uLogger\Tests\Mapper\Fixtures\UsersOnlyAdmin;

class ClientAPITest extends AbstractAPITestCase {

  /**
   * @throws DatabaseException
   */
  public function setUp(): void {
    parent::setUp();
    $this->insertFixtures([ UsersOnlyAdmin::class ]);
  }

  /**
   * @throws GuzzleException
   */
  public function testNoAction(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');

    $this->performRequestAndAssertResponseError([], 422);
  }

  /* auth */

  /**
   * @throws GuzzleException
   */
  public function testAuthOk(): void {
    $this->performRequestAndAssertResponseSuccess([ 'action' => 'auth', 'user' => self::TEST_ADMIN_USER, 'pass' => self::TEST_ADMIN_PASS ]);
  }

  /**
   * @throws GuzzleException
   */
  public function testAuthFail(): void {
    $this->performRequestAndAssertResponseNotAuthorized([ 'action' => 'auth', 'user' => 'noexist', 'pass' => 'noexist' ]);
  }

  /* addtrack */
  /**
   * @throws GuzzleException
   */
  public function testAddTrack(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $this->assertTableRowCount(0, 'tracks');

    $json = $this->performRequestAndAssertResponseSuccess([ 'action' => 'addtrack', 'track' => self::TEST_TRACK_NAME ]);

    $this->assertEquals(1, $json->{'trackid'}, 'Wrong track id');
    $this->assertTableRowCount(1, 'tracks');
    $expected = [ 'id' => 1, 'user_id' => 1, 'name' => self::TEST_TRACK_NAME ];
    $this->assertTableRow($expected, 'tracks', 1);
  }

  /**
   * @throws GuzzleException
   */
  public function testAddTrackEmptyName(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $this->assertTableRowCount(0, 'tracks');

    $json = $this->performRequestAndAssertResponseError([ 'action' => 'addtrack', 'track' => '' ]);

    $this->assertFalse(isset($json->{'trackid'}), 'Unexpected track id');
    $this->assertTableRowCount(0, 'tracks');
  }

  /**
   * @throws GuzzleException
   */
  public function testAddTrackNoParameters(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $this->assertTableRowCount(0, 'tracks');

    $json = $this->performRequestAndAssertResponseError([ 'action' => 'addtrack' ]);

    $this->assertFalse(isset($json->{'trackid'}), 'Unexpected track id');
    $this->assertTableRowCount(0, 'tracks');
  }

  /* addpos */
  /**
   * @throws GuzzleException
   */
  public function testAddPosition(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(0, 'positions');

    $this->performRequestAndAssertResponseSuccess([
      'action' => 'addpos',
      'trackid' => $trackId,
      'time' => self::TEST_TIMESTAMP,
      'lat' => self::TEST_LATITUDE,
      'lon' => self::TEST_LONGITUDE,
      'altitude' => self::TEST_ALTITUDE,
      'speed' => self::TEST_SPEED,
      'bearing' => self::TEST_BEARING,
      'accuracy' => self::TEST_ACCURACY,
      'provider' => self::TEST_PROVIDER,
      'comment' => self::TEST_COMMENT
    ]);

    $this->assertTableRowCount(1, 'positions');
    $expected = [
      'id' => 1,
      'time' => gmdate('Y-m-d H:i:s', self::TEST_TIMESTAMP),
      'user_id' => self::TEST_ADMIN_ID,
      'track_id' => $trackId,
      'latitude' => self::TEST_LATITUDE,
      'longitude' => self::TEST_LONGITUDE,
      'altitude' => self::TEST_ALTITUDE,
      'speed' => self::TEST_SPEED,
      'bearing' => self::TEST_BEARING,
      'accuracy' => self::TEST_ACCURACY,
      'provider' => self::TEST_PROVIDER,
      'comment' => self::TEST_COMMENT,
      'image' => null
    ];
    $this->assertTableRow($expected, 'positions', 1);
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionWithImage(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(0, 'positions');

    $payload = [
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
        'contents' => self::TEST_TIMESTAMP,
      ],
      [
        'name' => 'lat',
        'contents' => self::TEST_LATITUDE,
      ],
      [
        'name' => 'lon',
        'contents' => self::TEST_LONGITUDE,
      ],
      [
        'name' => 'altitude',
        'contents' => self::TEST_ALTITUDE,
      ],
      [
        'name' => 'speed',
        'contents' => self::TEST_SPEED,
      ],
      [
        'name' => 'bearing',
        'contents' => self::TEST_BEARING,
      ],
      [
        'name' => 'accuracy',
        'contents' => self::TEST_ACCURACY,
      ],
      [
        'name' => 'provider',
        'contents' => self::TEST_PROVIDER,
      ],
      [
        'name' => 'comment',
        'contents' => self::TEST_COMMENT,
      ],
      [
        'name' => 'image',
        'contents' => 'DEADBEEF',
        'filename' => 'upload',
        'headers' => [ 'Content-Type' => 'image/jpeg', 'Content-Transfer-Encoding' => 'binary' ]
      ]
    ];
    $this->performRequestAndValidate($payload, 200, false, 'multipart');

    $this->assertTableRowCount(1, 'positions');
    $expected = [
      'id' => 1,
      'user_id' => self::TEST_ADMIN_ID,
      'track_id' => $trackId,
      'time' => self::TEST_TIMESTAMP,
      'latitude' => self::TEST_LATITUDE,
      'longitude' => self::TEST_LONGITUDE,
      'altitude' => self::TEST_ALTITUDE,
      'speed' => self::TEST_SPEED,
      'bearing' => self::TEST_BEARING,
      'accuracy' => self::TEST_ACCURACY,
      'provider' => self::TEST_PROVIDER,
      'comment' => self::TEST_COMMENT
    ];

    $actual = $this->getTableRowById('positions', 1);
    $this->assertSame($expected['id'], $actual['id']);
    $this->assertSame($expected['user_id'], $actual['user_id']);
    $this->assertSame($expected['track_id'], $actual['track_id']);
    $this->assertSame(gmdate('Y-m-d H:i:s', $expected['time']), $actual['time']);
    $this->assertSame($expected['latitude'], $actual['latitude']);
    $this->assertSame($expected['longitude'], $actual['longitude']);
    $this->assertSame($expected['altitude'], $actual['altitude']);
    $this->assertSame($expected['speed'], $actual['speed']);
    $this->assertSame($expected['bearing'], $actual['bearing']);
    $this->assertSame($expected['accuracy'], $actual['accuracy']);
    $this->assertSame($expected['provider'], $actual['provider']);
    $this->assertSame($expected['comment'], $actual['comment']);
    $this->assertStringContainsString('.jpg', $actual['image']);
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionNonexistentTrack(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $this->assertTableRowCount(0, 'tracks');
    $this->assertTableRowCount(0, 'positions');

    $this->performRequestAndAssertResponseNotAuthorized([
      'action' => 'addpos',
      'trackid' => self::TEST_TRACK_ID,
      'time' => self::TEST_TIMESTAMP,
      'lat' => self::TEST_LATITUDE,
      'lon' => self::TEST_LONGITUDE,
      'altitude' => self::TEST_ALTITUDE,
      'speed' => self::TEST_SPEED,
      'bearing' => self::TEST_BEARING,
      'accuracy' => self::TEST_ACCURACY,
      'provider' => self::TEST_PROVIDER,
      'comment' => self::TEST_COMMENT,
      'imageid' => self::TEST_IMAGE
    ]);
    $this->assertTableRowCount(0, 'positions');
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionEmptyParameters(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(0, 'positions');

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $trackId,
        'time' => self::TEST_TIMESTAMP,
        'lat' => self::TEST_LATITUDE,
        'lon' => self::TEST_LONGITUDE,
        'altitude' => self::TEST_ALTITUDE,
        'speed' => self::TEST_SPEED,
        'bearing' => self::TEST_BEARING,
        'accuracy' => self::TEST_ACCURACY,
        'provider' => self::TEST_PROVIDER,
        'comment' => self::TEST_COMMENT,
        'imageid' => self::TEST_IMAGE
      ],
    ];

    // required
    foreach ([ 'trackid', 'time', 'lat', 'lon' ] as $parameter) {
      $optCopy = $options;
      $optCopy['form_params'][$parameter] = '';

      $response = $this->http->post('/client/index.php', $optCopy);

      $this->assertEquals(200, $response->getStatusCode(), 'Unexpected status code');
      $json = json_decode((string) $response->getBody());
      $this->assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    $this->assertTableRowCount(0, 'positions');
  }

  /**
   * @throws GuzzleException
   */
  public function testAddPositionMissingParameters(): void {
    $this->assertTrue($this->authenticateLegacy(), 'Authentication failed');
    $trackId = $this->addTestTrack(self::TEST_ADMIN_ID);
    $this->assertTableRowCount(1, 'tracks');
    $this->assertTableRowCount(0, 'positions');

    $options = [
      'http_errors' => false,
      'form_params' => [
        'action' => 'addpos',
        'trackid' => $trackId,
        'time' => self::TEST_TIMESTAMP,
        'lat' => self::TEST_LATITUDE,
        'lon' => self::TEST_LONGITUDE,
        'altitude' => self::TEST_ALTITUDE,
        'speed' => self::TEST_SPEED,
        'bearing' => self::TEST_BEARING,
        'accuracy' => self::TEST_ACCURACY,
        'provider' => self::TEST_PROVIDER,
        'comment' => self::TEST_COMMENT,
        'imageid' => self::TEST_IMAGE
      ],
    ];

    // required
    foreach ([ 'trackid', 'time', 'lat', 'lon' ] as $parameter) {
      $optCopy = $options;
      unset($optCopy['form_params'][$parameter]);

      $response = $this->http->post('/client/index.php', $optCopy);

      $this->assertEquals(200, $response->getStatusCode(), 'Unexpected status code');
      $json = json_decode((string) $response->getBody());
      $this->assertTrue($json->{'error'}, "Unexpected success ($parameter)");
    }
    $this->assertTableRowCount(0, 'positions');

    // optional
    $optional = [ 'altitude', 'speed', 'bearing', 'accuracy', 'provider', 'comment', 'imageid' ];
    foreach ($optional as $parameter) {
      $optCopy = $options;
      unset($optCopy['form_params'][$parameter]);

      $response = $this->http->post('/client/index.php', $optCopy);

      $this->assertEquals(200, $response->getStatusCode(), 'Unexpected status code');
      $json = json_decode((string) $response->getBody());
      $this->assertFalse($json->{'error'}, "Unexpected error ($parameter)");
    }
    $this->assertTableRowCount(count($optional), 'positions');

  }

  /**
   * @param array $payload
   * @param int $expectedStatusCode
   * @param bool|null $expectedError
   * @param string $payloadType
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  private function performRequestAndValidate(array $payload, int $expectedStatusCode, ?bool $expectedError = null, string $payloadType = 'form_params'): stdClass|array|null {
    $options = ['http_errors' => false];
    if (!empty($payload)) {
      $options[$payloadType] = $payload;
    }
    $response = $this->http->post('/client/index.php', $options);
    $this->assertEquals($expectedStatusCode, $response->getStatusCode(), 'Unexpected status code');
    $json = json_decode((string) $response->getBody());
    if (is_null($expectedError)) {
      $this->assertNull($json, 'Response is not null');
    } elseif ($expectedError) {
      $this->assertTrue($json->{'error'}, 'Unexpected success');
    } else {
      $this->assertFalse($json->{'error'}, 'Unexpected error');
    }
    return $json;
  }

  /**
   * @param array $payload
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  private function performRequestAndAssertResponseSuccess(array $payload = []): stdClass|array|null {
    return $this->performRequestAndValidate($payload, 200, false);
  }

  /**
   * @param array $payload
   * @param int $statusCode
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  private function performRequestAndAssertResponseError(array $payload = [], int $statusCode = 200): stdClass|array|null {
    return $this->performRequestAndValidate($payload, $statusCode, true);
  }

  /**
   * @param array $payload
   * @throws GuzzleException
   */
  private function performRequestAndAssertResponseNotAuthorized(array $payload = []): void {
    $this->performRequestAndValidate($payload, 401);
  }
}

?>
