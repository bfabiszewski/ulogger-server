<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/track.php");

class TrackTest extends UloggerDatabaseTestCase {

  public function testAddTrack() {
    $trackId = uTrack::add($this->testUserId, $this->testTrackName, $this->testTrackComment);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $trackId, "Wrong track id returned");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => $this->testTrackName, "comment" => $this->testTrackComment ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $this->assertFalse(uTrack::add("", $this->testTrackName), "Adding track with empty user id should fail");
    $this->assertFalse(uTrack::add($this->testUserId, ""), "Adding track with empty name should fail");
  }

  public function testDeleteTrack() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $track = new uTrack($trackId);
    $track->delete();
    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($track->isValid, "Deleted track should not be valid");
  }

  public function testAddPosition() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $track = new uTrack($trackId + 1);
    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with nonexistant track should fail");

    $track = new uTrack($trackId);
    $posId = $track->addPosition($this->testUserId2, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with wrong user should fail");

    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $expected = [
      "id" => $posId,
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
      "image_id" => $this->testImageId
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, user_id, track_id, UNIX_TIMESTAMP(time) AS time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image_id FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $posId = $track->addPosition($this->testUserId, NULL, $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with null time stamp should fail");
    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, NULL, $this->testLon);
    $this->assertFalse($posId, "Adding position with null latitude should fail");
    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, $this->testLat, NULL);
    $this->assertFalse($posId, "Adding position with null longitude should fail");

    $posId = $track->addPosition($this->testUserId, "", $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with empty time stamp should fail");
    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, "", $this->testLon);
    $this->assertFalse($posId, "Adding position with empty latitude should fail");
    $posId = $track->addPosition($this->testUserId, $this->testTimestamp, $this->testLat, "");
    $this->assertFalse($posId, "Adding position with empty longitude should fail");
  }

  public function testGetAll() {
    $this->addTestTrack();
    $this->addTestTrack();
    $this->assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $trackArr = uTrack::getAll();
    $this->assertEquals(2, count($trackArr), "Wrong array size");
    $this->assertTrue($trackArr[0] instanceof uTrack, "Wrong array member");
  }

  public function testDeleteAll() {
    $trackId = $this->addTestTrack();
    $this->addTestTrack();
    $this->addTestPosition($this->testUserId, $trackId);

    $trackId2 = $this->addTestTrack($this->testUserId2);
    $this->addTestPosition($this->testUserId2, $trackId2);

    $this->assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    uTrack::deleteAll($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse(uTrack::deleteAll(NULL), "User id should not be empty");
  }

  public function testUpdate() {
    $trackId = $this->addTestTrack();
    $track = new uTrack($trackId);
    $track->update("newName", "newComment");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => "newName", "comment" => "newComment" ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $trackInvalid = new uTrack($trackId + 1);
    $this->assertFalse($trackInvalid->update("newName", "newComment"), "Updating nonexistant track should fail");
  }

  public function testIsValid() {
    $trackId = $this->addTestTrack();
    $trackValid = new uTrack($trackId);
    $this->assertTrue($trackValid->isValid, "Track should be valid");
    $trackInvalid = new uTrack($trackId + 1);
    $this->assertFalse($trackInvalid->isValid, "Track should not be valid");
  }
}
?>
