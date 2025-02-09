<?php
declare(strict_types = 1);

namespace uLogger\Tests\_tests;

use uLogger\Entity\Position;
use uLogger\Tests\_lib\AssertExceptionTrait;
use uLogger\Tests\_lib\UloggerDatabaseTestCase;

class PositionTest extends UloggerDatabaseTestCase {

  use AssertExceptionTrait;

  public function testAddPosition(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $posId = Position::add($userId, $trackId + 1, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($posId, "Adding position with nonexistant track should fail");

    $posId = Position::add($userId + 1, $trackId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($posId, "Adding position with wrong user should fail");

    $posId = Position::add($userId, $trackId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
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

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, null, $this->testLat, $this->testLon); }, "Adding position with null time stamp should fail");

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, $this->testTimestamp, null, $this->testLon); }, "Adding position with null latitude should fail");

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, $this->testTimestamp, $this->testLat, null); }, "Adding position with null longitude should fail");

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, "", $this->testLat, $this->testLon); }, "Adding position with empty time stamp should fail");

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, $this->testTimestamp, "", $this->testLon); }, "Adding position with empty latitude should fail");

    self::assertTypeError(function () use ($userId, $trackId) {
      /** @noinspection PhpParamsInspection */
      Position::add($userId, $trackId, $this->testTimestamp, $this->testLat, ""); }, "Adding position with empty longitude should fail");
  }

  public function testDeleteAll(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2);
    $trackId3 = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId3);
    self::assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    self::assertTrue(Position::deleteAll($userId), "Deleting failed");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testDeleteAllWIthTrackId(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2);
    $trackId3 = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId3);
    self::assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    self::assertTrue(Position::deleteAll($userId, $trackId), "Deleting failed");
    self::assertEquals(2, $this->getConnection()->getRowCount('positions'), "Wrong row count");
  }

  public function testGetLast(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId1 = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);
    $pos1 = $this->addTestPosition($userId, $trackId1, $this->testTimestamp + 3);
    $pos2 = $this->addTestPosition($userId2, $trackId2, $this->testTimestamp + 1);
    $pos3 = $this->addTestPosition($userId, $trackId1, $this->testTimestamp);
    $pos4 = $this->addTestPosition($userId2, $trackId2, $this->testTimestamp + 2);
    self::assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(4, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $lastPosition = Position::getLast();
    self::assertEquals($lastPosition->id, $pos1, "Wrong last position");
    $lastPosition = Position::getLast($this->testUserId2);
    self::assertEquals($lastPosition->id, $pos4, "Wrong last position (user)");
  }

  public function testGetLastAllUsers(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId1 = $this->addTestTrack($userId);
    $trackId2 = $this->addTestTrack($userId);
    $pos1 = $this->addTestPosition($userId, $trackId1, $this->testTimestamp + 3);
    $pos2 = $this->addTestPosition($userId2, $trackId2, $this->testTimestamp + 1);
    $pos3 = $this->addTestPosition($userId, $trackId1, $this->testTimestamp);
    $pos4 = $this->addTestPosition($userId2, $trackId2, $this->testTimestamp + 2);
    self::assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(4, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $posArr = Position::getLastAllUsers();
    self::assertCount(2, $posArr, "Wrong row count");
    foreach ($posArr as $position) {
      /** @var Position $position */
      switch ($position->id) {
        case 1:
          self::assertEquals($this->testTimestamp + 3, $position->timestamp);
          self::assertEquals($userId, $position->userId);
          self::assertEquals($trackId1, $position->trackId);
          break;
        case 4:
          self::assertEquals($this->testTimestamp + 2, $position->timestamp);
          self::assertEquals($userId2, $position->userId);
          self::assertEquals($trackId2, $position->trackId);
          break;
        default:
          self::fail("Unexpected position: $position->id");
      }
    }
  }

  public function testGetAll(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $userId3 = $this->addTestUser("testUser3");
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);
    $trackId2 = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId2);
    $trackId3 = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId3);
    self::assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(3, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $posArr = Position::getAll();
    self::assertCount(3, $posArr, "Wrong row count");
    $posArr = Position::getAll($userId);
    self::assertCount(2, $posArr, "Wrong row count");
    $posArr = Position::getAll($userId, $trackId);
    self::assertCount(1, $posArr, "Wrong row count");
    $posArr = Position::getAll(null, $trackId);
    self::assertCount(1, $posArr, "Wrong row count");
    $posArr = Position::getAll($userId3);
    self::assertCount(0, $posArr, "Wrong row count");
  }

  public function testDistanceTo(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $pos1 = $this->addTestPosition($userId, $trackId, $this->testTimestamp, 0, 0);
    $pos2 = $this->addTestPosition($userId, $trackId, $this->testTimestamp, 0, 1);
    $posArr = Position::getAll();
    self::assertCount(2, $posArr, "Wrong row count");
    self::assertEquals(111195, round($posArr[0]->distanceTo($posArr[1])), "Wrong distance");
  }

  public function testSecondsTo(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $pos1 = $this->addTestPosition($userId, $trackId, $this->testTimestamp);
    $pos2 = $this->addTestPosition($userId, $trackId, $this->testTimestamp + 1);
    $posArr = Position::getAll();
    self::assertCount(2, $posArr, "Wrong row count");
    self::assertEquals(-1, $posArr[0]->secondsTo($posArr[1]), "Wrong time difference");
  }

}

?>
