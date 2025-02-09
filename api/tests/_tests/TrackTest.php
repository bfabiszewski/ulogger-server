<?php
declare(strict_types = 1);

namespace uLogger\Tests\_tests;

use uLogger\Entity\Track;
use uLogger\Tests\_lib\AssertExceptionTrait;
use uLogger\Tests\_lib\UloggerDatabaseTestCase;

class TrackTest extends UloggerDatabaseTestCase {

  use AssertExceptionTrait;

  public function testAddTrack(): void {
    $this->addTestUser();
    $trackId = Track::add($this->testUserId, $this->testTrackName, $this->testTrackComment);
    self::assertNotFalse($trackId, "Track id should not be false");
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(1, $trackId, "Wrong track id returned");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => $this->testTrackName, "comment" => $this->testTrackComment ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    self::assertTypeError(function () {
      /** @noinspection PhpStrictTypeCheckingInspection */
      Track::add("", $this->testTrackName); }, "Adding track with empty user id should fail");
    self::assertFalse(Track::add($this->testUserId, ""), "Adding track with empty name should fail");
  }

  public function testDeleteTrack(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $track = new Track($trackId);
    $track->delete();
    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($track->isValid, "Deleted track should not be valid");
  }

  public function testAddPosition(): void {
    $userId = $this->addTestUser();
    $userId2 = $this->addTestUser($this->testUser2);
    $trackId = $this->addTestTrack($userId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $track = new Track($trackId + 1);
    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($posId, "Adding position with nonexistant track should fail");

    $track = new Track($trackId);
    $posId = $track->addPosition($userId2, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($posId, "Adding position with wrong user should fail");

    $posId = $track->addPosition($userId, $this->testTimestamp, $this->testLat, $this->testLon, $this->testAltitude, $this->testSpeed, $this->testBearing, $this->testAccuracy, $this->testProvider, $this->testComment, $this->testImage);
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

    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, null, $this->testLat, $this->testLon); }, "Adding position with null time stamp should fail");
    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, $this->testTimestamp, null, $this->testLon); }, "Adding position with null latitude should fail");
    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, $this->testTimestamp, $this->testLat, null); }, "Adding position with null longitude should fail");

    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, "", $this->testLat, $this->testLon); }, "Adding position with empty time stamp should fail");
    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, $this->testTimestamp, "", $this->testLon); }, "Adding position with empty latitude should fail");
    self::assertTypeError(function () use ($track, $userId) {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      $track->addPosition($userId, $this->testTimestamp, $this->testLat, ""); }, "Adding position with empty longitude should fail");
  }

  public function testGetAll(): void {
    $this->addTestTrack($this->addTestUser());
    $this->addTestTrack($this->addTestUser($this->testUser2));
    self::assertEquals(2, $this->getConnection()->getRowCount('tracks'), "Wrong row count");

    $trackArr = Track::getAll();
    self::assertCount(2, $trackArr, "Wrong array size");
    self::assertInstanceOf(Track::class, $trackArr[0], "Wrong array member");
  }

  public function testDeleteAll(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);

    $userId2 = $this->addTestUser($this->testUser2);
    $trackId2 = $this->addTestTrack($userId2);
    $this->addTestPosition($userId2, $trackId2);

    self::assertEquals(3, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(2, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    Track::deleteAll($userId);
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertTypeError(function () {
      /** @noinspection PhpStrictTypeCheckingInspection */
      /** @noinspection PhpParamsInspection */
      Track::deleteAll(null); }, "User id should not be empty");
  }

  public function testUpdate(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $track = new Track($trackId);
    $track->update("newName", "newComment");
    $expected = [ "id" => $trackId, "user_id" => $this->testUserId, "name" => "newName", "comment" => "newComment" ];
    $actual = $this->getConnection()->createQueryTable("tracks", "SELECT id, user_id, name, comment FROM tracks");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $trackInvalid = new Track($trackId + 1);
    self::assertFalse($trackInvalid->update("newName", "newComment"), "Updating nonexistant track should fail");
  }

  public function testIsValid(): void {
    $userId = $this->addTestUser();
    $trackId = $this->addTestTrack($userId);
    $trackValid = new Track($trackId);
    self::assertTrue($trackValid->isValid, "Track should be valid");
    $trackInvalid = new Track($trackId + 1);
    self::assertFalse($trackInvalid->isValid, "Track should not be valid");
  }
}

?>
