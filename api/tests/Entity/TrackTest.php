<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\TestCase;
use uLogger\Entity\Track;

class TrackTest extends TestCase  {

  public function testConstructorSetsProperties(): void {
    $userId = 1;
    $trackName = 'Test Track';
    $comment = 'Sample Comment';

    $track = new Track($userId, $trackName, $comment);
    $this->assertSame($userId, $track->userId);
    $this->assertSame($trackName, $track->name);
    $this->assertSame($comment, $track->comment);
  }

  public function testDefaultValues(): void {
    $userId = 1;
    $trackName = 'Test Track';
    $track = new Track($userId, $trackName);

    $this->assertNull($track->id);
    $this->assertSame($userId, $track->userId);
    $this->assertSame($trackName, $track->name);
    $this->assertNull($track->comment);
  }
}

?>
