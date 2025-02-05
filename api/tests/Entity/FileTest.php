<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\TestCase;
use uLogger\Entity\File;

class FileTest extends TestCase {

  public function testSetAndGetFileName(): void {
    $file = new File();
    $fileName = 'test.txt';
    $file->setFileName($fileName);
    $this->assertSame($fileName, $file->getFileName());
  }

  public function testSetAndGetContent(): void {
    $file = new File();
    $content = 'sample content';
    $file->setContent($content);
    $this->assertSame($content, $file->getContent());
  }

  public function testSetAndGetMimeType(): void {
    $file = new File();
    $mimeType = 'text/plain';
    $file->setMimeType($mimeType);
    $this->assertSame($mimeType, $file->getMimeType());
  }

  public function testSetAndGetPath(): void {
    $file = new File();
    $path = '/uploads/test.txt';
    $file->setPath($path);
    $this->assertSame($path, $file->getPath());
  }

  public function testIsKnownMime(): void {
    $this->assertTrue(File::isKnownMime('image/jpeg'));
    $this->assertFalse(File::isKnownMime('application/pdf'));
  }

  public function testGetExtension(): void {
    $this->assertSame('jpg', File::getExtension('image/jpeg'));
    $this->assertNull(File::getExtension('application/pdf'));
  }

}

?>
