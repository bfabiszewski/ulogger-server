<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/track.php");

class PositionTest extends UloggerDatabaseTestCase {

  public function testAddPosition() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $posId = uPosition::add($this->testUserId, $trackId + 1, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with nonexistant track should fail");

    $posId = uPosition::add($this->testUserId2, $trackId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($posId, "Adding position with wrong user should fail");

    $posId = uPosition::add($this->testUserId, $trackId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImageId);
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

    $posId = uPosition::add($this->testUserId, $trackId, NULL, $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with null time stamp should fail");
    $posId = uPosition::add($this->testUserId, $trackId, $this->testTimestamp, NULL, $this->testLon);
    $this->assertFalse($posId, "Adding position with null latitude should fail");
    $posId = uPosition::add($this->testUserId, $trackId, $this->testTimestamp, $this->testLat, NULL);
    $this->assertFalse($posId, "Adding position with null longitude should fail");

    $posId = uPosition::add($this->testUserId, $trackId, "", $this->testLat, $this->testLon);
    $this->assertFalse($posId, "Adding position with empty time stamp should fail");
    $posId = uPosition::add($this->testUserId, $trackId, $this->testTimestamp, "", $this->testLon);
    $this->assertFalse($posId, "Adding position with empty latitude should fail");
    $posId = uPosition::add($this->testUserId, $trackId, $this->testTimestamp, $this->testLat, "");
    $this->assertFalse($posId, "Adding position with empty longitude should fail");
  }

  public function testDeleteAll() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId);
    $trackId2 = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId2);
    $trackId3 = $this->addTestTrack($this->testUserId2);
    $this->addTestPosition($this->testUserId2, $trackId3);
    $this->assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $this->assertTrue(uPosition::deleteAll($this->testUserId), "Deleting failed");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testDeleteAllWIthTrackId() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId);
    $trackId2 = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId2);
    $trackId3 = $this->addTestTrack($this->testUserId2);
    $this->addTestPosition($this->testUserId2, $trackId3);
    $this->assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $this->assertTrue(uPosition::deleteAll($this->testUserId, $trackId), "Deleting failed");
    $this->assertEquals(2, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testGetLast() {
    $trackId1 = $this->addTestTrack($this->testUserId);
    $trackId2 = $this->addTestTrack($this->testUserId);
    $pos1 = $this->addTestPosition($this->testUserId, $trackId1, $this->testTimestamp + 3);
    $pos2 = $this->addTestPosition($this->testUserId2, $trackId2, $this->testTimestamp + 1);
    $pos3 = $this->addTestPosition($this->testUserId, $trackId1, $this->testTimestamp);
    $pos4 = $this->addTestPosition($this->testUserId2, $trackId2, $this->testTimestamp + 2);
    $this->assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(4, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $lastPosition = uPosition::getLast();
    $this->assertEquals($lastPosition->id, $pos1, "Wrong last position");
    $lastPosition = uPosition::getLast($this->testUserId2);
    $this->assertEquals($lastPosition->id, $pos4, "Wrong last position (user)");
  }

  public function testGetAll() {
    $trackId = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId);
    $trackId2 = $this->addTestTrack($this->testUserId);
    $this->addTestPosition($this->testUserId, $trackId2);
    $trackId3 = $this->addTestTrack($this->testUserId2);
    $this->addTestPosition($this->testUserId2, $trackId3);
    $this->assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $posArr = uPosition::getAll();
    $this->assertEquals(3, count($posArr), "Wrong row count");
    $posArr = uPosition::getAll($this->testUserId);
    $this->assertEquals(2, count($posArr), "Wrong row count");
    $posArr = uPosition::getAll($this->testUserId, $trackId);
    $this->assertEquals(1, count($posArr), "Wrong row count");
    $posArr = uPosition::getAll(NULL, $trackId);
    $this->assertEquals(1, count($posArr), "Wrong row count");
    $posArr = uPosition::getAll($this->testUserId3);
    $this->assertEquals(0, count($posArr), "Wrong row count");
  }

  public function testDistanceTo() {
    $trackId = $this->addTestTrack($this->testUserId);
    $pos1 = $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp, 0, 0);
    $pos2 = $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp, 0, 1);
    $posArr = uPosition::getAll();
    $this->assertEquals(2, count($posArr), "Wrong row count");
    $this->assertEquals(111195, round($posArr[0]->distanceTo($posArr[1])), "Wrong distance");
  }

  public function testSecondsTo() {
    $trackId = $this->addTestTrack($this->testUserId);
    $pos1 = $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp);
    $pos2 = $this->addTestPosition($this->testUserId, $trackId, $this->testTimestamp + 1);
    $posArr = uPosition::getAll();
    $this->assertEquals(2, count($posArr), "Wrong row count");
    $this->assertEquals(-1, $posArr[0]->secondsTo($posArr[1]), "Wrong time difference");
  }

}
?>
