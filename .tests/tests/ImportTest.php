<?php /** @noinspection HtmlUnknownAttribute */

use Psr\Http\Message\ResponseInterface;

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");
if (!defined("ROOT_DIR")) { define("ROOT_DIR", __DIR__ . "/../.."); }
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");

class ImportTest extends UloggerAPITestCase {

  public function testImportGPX10() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx10 = '<?xml version="1.0"?>
    <gpx version="1.0" creator="test software"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0
    http://www.topografix.com/GPX/1/0/gpx.xsd">
      <time>2017-09-19T11:00:08Z</time>
      <trk>
        <name>' . $this->testTrackName . '</name>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '">
            <ele>' . $this->testAltitude . '</ele>
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp) . '</time>
          </trkpt>
          <trkpt lat="' . $this->testLat . '" lon="' . -1 * $this->testLon . '">
            <ele>' . $this->testAltitude . '</ele>
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp + 1) . '</time>
          </trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx10)
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());
    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => $this->testTimestamp,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => $this->testAltitude,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image  FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $expected = [
      "id" => 2,
      "time" => $this->testTimestamp + 1,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon * -1,
      "altitude" => $this->testAltitude,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportGPX11() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx11 = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
    <gpx version="1.1"
        creator="test creator"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns="http://www.topografix.com/GPX/1/1"
        xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
      <metadata>
        <name>' . $this->testTrackComment . '</name>
        <desc>Track for testing ulogger</desc>
        <author>
          <name>Bartek Fabiszewski</name>
          <link href="http://www.fabiszewski.net"><text>fabiszewski.net</text></link>
        </author>
        <time>2017-09-19T09:22:06Z</time>
        <keywords>Test, ulogger</keywords>
        <bounds minlat="44.64365345" maxlat="45.341" minlon="14.452354" maxlon="24.54234"/>
      </metadata>
      <trk>
        <src>Crafted by Bartek Fabiszewski</src>
        <link href="http://www.fabiszewski.net"><text>fabiszewski.net</text></link>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '">
            <ele>' . $this->testAltitude . '</ele>
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp) . '</time>
            <fix>3d</fix>
            <hdop>300</hdop><vdop>300</vdop><pdop>300</pdop>
          </trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx11),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => $this->testTrackComment
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => $this->testTimestamp,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => $this->testAltitude,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportExtensions() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1
    http://www.topografix.com/GPX/1/1/gpx.xsd
    https://github.com/bfabiszewski/ulogger-android/1
    https://github.com/bfabiszewski/ulogger-android/gpx_extensions1.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1"
    creator="μlogger" version="1.1">
      <metadata>
      <name>' . $this->testTrackComment . '</name>
      <time>2017-06-24T22:50:45Z</time>
      </metadata>
      <trk>
      <name>' . $this->testTrackName . '</name>
      <trkseg>
        <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '">
        <ele>' . $this->testAltitude . '</ele>
        <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp) . '</time>
        <name>1</name>
        <desc><![CDATA[' . $this->testComment . ']]></desc>
        <extensions>
          <ulogger:speed>' . $this->testSpeed . '</ulogger:speed>
          <ulogger:bearing>' . $this->testBearing . '</ulogger:bearing>
          <ulogger:accuracy>' . $this->testAccuracy . '</ulogger:accuracy>
          <ulogger:provider>' . $this->testProvider . '</ulogger:provider>
        </extensions>
        </trkpt>
        </trkseg>
      </trk>
      </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => $this->testTrackComment
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => $this->testTimestamp,
      "user_id" => 1,
      "track_id" => 1,
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
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportNoTime() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1
    http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '"></trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => 1,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => null,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportMultipleSegments() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '">
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp) . '</time>
          </trkpt>
        </trkseg>
        <trkseg>
          <trkpt lat="' . ($this->testLat + 1) . '" lon="' . ($this->testLon + 1) . '">
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp + 1) . '</time>
          </trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 1, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(1, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => $this->testTimestamp,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => null,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 2,
      "time" => $this->testTimestamp + 1,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat + 1,
      "longitude" => $this->testLon + 1,
      "altitude" => null,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportMultipleTracks() {

    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '">
            <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp) . '</time>
          </trkpt>
        </trkseg>
      </trk>
      <trk>
      <trkseg>
        <trkpt lat="' . ($this->testLat + 1) . '" lon="' . ($this->testLon + 1) . '">
          <time>' . gmdate("Y-m-d\TH:i:s\Z", $this->testTimestamp + 1) . '</time>
        </trkpt>
      </trkseg>
    </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(count($json), 2, "Wrong count of tracks");

    $track = $json[0];
    $this->assertEquals(2, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $track = $json[1];
    $this->assertEquals(1, (int) $track->id, "Wrong track id");
    $this->assertEquals($this->testTrackName, $track->name, "Wrong track name");

    $this->assertEquals(2, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $expected = [
      "id" => 1,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "tracks",
      "SELECT * FROM tracks"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 2,
      "user_id" => 1,
      "name" => $this->testTrackName,
      "comment" => null
    ];
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 1,
      "time" => $this->testTimestamp,
      "user_id" => 1,
      "track_id" => 1,
      "latitude" => $this->testLat,
      "longitude" => $this->testLon,
      "altitude" => null,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, " . $this->unix_timestamp('time') . " AS time, user_id, track_id, latitude, longitude,
      altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $expected = [
      "id" => 2,
      "time" => $this->testTimestamp + 1,
      "user_id" => 1,
      "track_id" => 2,
      "latitude" => $this->testLat + 1,
      "longitude" => $this->testLon + 1,
      "altitude" => null,
      "speed" => null,
      "bearing" => null,
      "accuracy" => null,
      "provider" => "gps",
      "comment" => null,
      "image" => null
    ];
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
  }

  public function testImportNoLongitude() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1
    http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lat="' . $this->testLat . '"></trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, (int) $json->error, "Wrong error status");
    $this->assertEquals($lang["iparsefailure"], (string) $json->message, "Wrong error status");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");
  }

  public function testImportNoLatitude() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1
    http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lon="' . $this->testLon . '"></trkpt>
        </trkseg>
      </trk>
    </gpx>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, (int) $json->error, "Wrong error status");
    $this->assertEquals($lang["iparsefailure"], (string) $json->message, "Wrong error status");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");
  }

  public function testImportNoGPX() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
      <trk>
        <trkseg>
          <trkpt lat="' . $this->testLat . '" lon="' . $this->testLon . '"></trkpt>
        </trkseg>
      </trk>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, (int) $json->error, "Wrong error status");
    $this->assertEquals($lang["iparsefailure"], (string) $json->message, "Wrong error status");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");
  }

  public function testImportCorrupt() {
    $lang = (new uLang($this->mockConfig))->getStrings();
    $this->assertTrue($this->authenticate(), "Authentication failed");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");

    $gpx = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1
    http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    creator="μlogger" version="1.1">
      <trk>
        <trkseg>
          <trkpt lon="' . $this->testLon . '"></trkpt>
        </trkseg>';

    $options = [
      "http_errors" => false,
      "multipart" => [
        [
          "name" => "gpx",
          "contents" => $this->getStream($gpx),
          "filename" => $this->testTrackName
        ],
        [
          "name" => "MAX_FILE_SIZE",
          "contents" => 300000
        ]
      ],
    ];
    $response = $this->http->post("/utils/import.php", $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");

    $json = json_decode($response->getBody());

    $this->assertNotNull($json, "JSON object is null");
    $this->assertEquals(1, (int) $json->error, "Wrong error status");
    $this->assertEquals(0, strpos((string) $json->message, $lang["iparsefailure"]), "Wrong error status");

    $this->assertEquals(0, $this->getConnection()->getRowCount("tracks"), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount("positions"), "Wrong row count");
  }

  private function getStream($string) {
    $stream = tmpfile();
    fwrite($stream, $string);
    fseek($stream, 0);
    return $stream;
  }

}

?>
