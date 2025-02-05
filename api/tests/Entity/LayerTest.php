<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\TestCase;
use uLogger\Entity\Layer;

class LayerTest extends TestCase {

  public function testConstructorSetsProperties(): void {
    $name = 'Test Layer';
    $url = 'http://example.com';
    $priority = 10;
    $layer = new Layer(1, $name, $url, $priority);

    $this->assertSame(1, $layer->id);
    $this->assertSame($name, $layer->name);
    $this->assertSame($url, $layer->url);
    $this->assertSame($priority, $layer->priority);
  }
}
?>
