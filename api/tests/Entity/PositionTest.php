<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\TestCase;
use uLogger\Entity\Position;

class PositionTest extends TestCase {

  public function testConstructorSetsProperties(): void {
    $timestamp = 1700000000;
    $userId = 1;
    $trackId = 2;
    $latitude = 50.0;
    $longitude = 20.0;

    $position = new Position($timestamp, $userId, $trackId, $latitude, $longitude);

    $this->assertSame($timestamp, $position->timestamp);
    $this->assertSame($userId, $position->userId);
    $this->assertSame($trackId, $position->trackId);
    $this->assertSame($latitude, $position->latitude);
    $this->assertSame($longitude, $position->longitude);
  }

  public function testDefaultValues(): void {
    $position = new Position(1700000000, 1, 2, 50.0, 20.0);

    $this->assertNull($position->id);
    $this->assertNull($position->userName);
    $this->assertNull($position->trackName);
    $this->assertNull($position->altitude);
    $this->assertNull($position->speed);
    $this->assertNull($position->bearing);
    $this->assertNull($position->accuracy);
    $this->assertNull($position->provider);
    $this->assertNull($position->comment);
    $this->assertNull($position->image);
    $this->assertFalse($position->hasImage);
    $this->assertSame(0, $position->meters);
    $this->assertSame(0, $position->seconds);
  }

  public function testHasImageReturnsTrueWhenImageIsSet(): void {
    $position = new Position(1700000000, 1, 2, 50.0, 20.0);
    $position->image = 'image.jpg';

    $this->assertTrue($position->hasImage());
  }

  public function testHasImageReturnsFalseWhenImageIsNull(): void {
    $position = new Position(1700000000, 1, 2, 50.0, 20.0);

    $this->assertFalse($position->hasImage());
  }

  public function testDistanceToCalculatesCorrectly(): void {
    $pos1 = new Position(1700000000, 1, 2, 0.0, 20.0);
    $pos2 = new Position(1700000001, 1, 2, 0.0, 21.0);

    $distance = $pos1->distanceTo($pos2);

    // one degree on Equator is around 111 km
    $this->assertEquals(111, round($distance / 1000));
  }

  public function testSecondsToCalculatesCorrectly(): void {
    $pos1 = new Position(1700000000, 1, 2, 50.0, 20.0);
    $pos2 = new Position(1700000010, 1, 2, 50.0, 20.0);

    $this->assertSame(-10, $pos1->secondsTo($pos2));
    $this->assertSame(10, $pos2->secondsTo($pos1));
  }
}

?>
