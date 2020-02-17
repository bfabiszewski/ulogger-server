<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/track.php");

class TrackTest extends UloggerDatabaseTestCase {

  public function testAddTrack() {
    $this->addTestUser();
    $trackId = uTrack::add($this->testUserId, $this->testTrackName, $this->testTrackComment);
    $this->assertNotFalse($trackId, "Track id should not be false");
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $trackId, "Wrong track id returned");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => $this->testTrackName, "comment" => $this->testTrackComment ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $this->assertFalse(uTrack::add("", $this->testTrackName), "Adding track with empty user id should fail");
    $this->assertFalse(uTrack::add($this->testUserId, ""), "Adding track with empty name should fail");
  }

  public function testDeleteTrack() {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $track = new uTrack($trackId);
    $track->delete();
    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($track->isValid, "Deleted track should not be valid");
  }

  public function testAddPosition() {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId = $this->addTestTrack($userId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $track = new uTrack($trackId + 1);
    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with nonexistant track should fail");

    $track = new uTrack($trackId);
    $posId = $track->addPosition($userId2, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with wrong user should fail");

    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
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
      "image" => $this->testImage
    ];
    $actual = $this->getConnection()->createQueryTable(
      "positions",
      "SELECT id, user_id, track_id, " . $this->unix_timestamp('time') . " AS time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image FROM positions"
    );
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $posId = $track->addPosition($userId, NULL, $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with null time stamp should fail");
    $posId = $track->addPosition($userId, $this->testTimestamp, NULL, $this->testLon);
    $this->assertFalse($posId, "Adding position with null latitude should fail");
    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, NULL);
    $this->assertFalse($posId, "Adding position with null longitude should fail");

    $posId = $track->addPosition($userId, "", $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with empty time stamp should fail");
    $posId = $track->addPosition($userId, $this->testTimestamp, "", $this->testLon);
    $this->assertFalse($posId, "Adding position with empty latitude should fail");
    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, "");
    $this->assertFalse($posId, "Adding position with empty longitude should fail");
  }

  public function testGetAll() {
    $this->addTestTrack($this->addTestUser());
    $this->addTestTrack($this->addTestUser($this->testUser2));
    $this->assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $trackArr = uTrack::getAll();
    $this->assertEquals(2, count($trackArr), "Wrong array size");
    $this->assertTrue($trackArr[0] instanceof uTrack, "Wrong array member");
  }

  public function testDeleteAll() {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);

    $userId2 = $this->addTestUser($this->testUser2);
    $trackId2 = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId2);

    $this->assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(2, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    uTrack::deleteAll($userId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse(uTrack::deleteAll(NULL), "User id should not be empty");
  }

  public function testUpdate() {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $track = new uTrack($trackId);
    $track->update("newName", "newComment");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => "newName", "comment" => "newComment" ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $trackInvalid = new uTrack($trackId + 1);
    $this->assertFalse($trackInvalid->update("newName", "newComment"), "Updating nonexistant track should fail");
  }

  public function testIsValid() {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $trackValid = new uTrack($trackId);
    $this->assertTrue($trackValid->isValid, "Track should be valid");
    $trackInvalid = new uTrack($trackId + 1);
    $this->assertFalse($trackInvalid->isValid, "Track should not be valid");
  }
}
?>
