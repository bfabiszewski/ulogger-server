<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Component;

use PHPUnit\Framework\TestCase;
use uLogger\Component\FileUpload;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Utils;

final class FileUploadTest extends TestCase {

  public function testConstructorAndGetters(): void {
    $name = 'example.txt';
    $fullPath = '/path/to/example.txt';
    $type = 'text/plain';
    $tmpName = '/tmp/example.txt';
    $error = 0;
    $size = 1234;
    $fileMeta = [
      'name' => $name,
      'full_path' => $fullPath,
      'type' => $type,
      'tmp_name' => $tmpName,
      'error' => $error,
      'size' => $size,
    ];
    $upload = new FileUpload($fileMeta);
    $this->assertSame($name, $upload->getName());
    $this->assertSame($fullPath, $upload->getFullPath());
    $this->assertSame($type, $upload->getType());
    $this->assertSame($tmpName, $upload->getTmpName());
    $this->assertSame($error, $upload->getError());
    $this->assertSame($size, $upload->getSize());
  }

  public function testFromBuffer(): void {
    $buffer = 'Hello, world!';
    $name = 'greeting.txt';
    $type = 'text/plain';

    $upload = FileUpload::fromBuffer($buffer, $name, $type);
    // fromBuffer sets full_path to empty string.
    $this->assertSame($name, $upload->getName());
    $this->assertSame('', $upload->getFullPath());
    $this->assertSame($type, $upload->getType());
    $this->assertSame(0, $upload->getError());
    $this->assertSame(strlen($buffer), $upload->getSize());

    // The temporary file created should exist and contain the buffer.
    $tmpName = $upload->getTmpName();
    $this->assertFileExists($tmpName);
    $this->assertSame($buffer, file_get_contents($tmpName));

    unlink($tmpName);
  }

  /**
   * @throws InvalidInputException
   */
  public function testSanitizeUploadValid(): void {

    $buffer = 'Valid file content';
    $name = 'test.jpg';
    $type = 'image/jpg';
    $upload = FileUpload::fromBuffer($buffer, $name, $type);

    $this->expectNotToPerformAssertions();
    $upload->sanitizeUpload();

    if (file_exists($upload->getTmpName())) {
      unlink($upload->getTmpName());
    }
  }

  public function testSanitizeUploadFileNotFound(): void {
    $fileMeta = [
      'name' => 'missing.txt',
      'full_path' => '',
      'type' => 'text/plain',
      'tmp_name' => '/nonexistent/file.tmp',
      'error' => 0,
      'size' => 10,
    ];
    $upload = new FileUpload($fileMeta);
    $this->expectException(InvalidInputException::class);
    $this->expectExceptionMessage('File not found');
    $upload->sanitizeUpload();
  }

  public function testSanitizeUploadUnsupportedMime(): void {
    // Create a temporary file.
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmpFile, 'content');

    $fileMeta = [
      'name' => 'file.bin',
      'full_path' => '',
      'type' => 'application/unknown', // unsupported mime type
      'tmp_name' => $tmpFile,
      'error' => 0,
      'size' => filesize($tmpFile),
    ];
    $upload = new FileUpload($fileMeta);
    $this->expectException(InvalidInputException::class);
    $this->expectExceptionMessage('Unsupported mime type');
    $upload->sanitizeUpload();

    if (file_exists($tmpFile)) {
      unlink($tmpFile);
    }
  }

  /**
   * @throws ServerException
   * @throws InvalidInputException
   */
  public function testAddMovesFile(): void {
    $buffer = 'File to be moved';
    $name = 'move.jpg';
    $type = 'image/jpg';
    $trackId = 123;

    // Create a FileUpload from buffer. fromBuffer() uses tempnam() with the proper prefix.
    $upload = FileUpload::fromBuffer($buffer, $name, $type);
    $tmpFile = $upload->getTmpName();
    $this->assertFileExists($tmpFile);

    $newFileName = $upload->add($trackId);
    // Expected new file name should begin with "$trackId_" and end with ".txt".
    $this->assertMatchesRegularExpression("/^{$trackId}_.*\\.jpg\$/", $newFileName);

    $destinationPath = rtrim(Utils::getUploadDir(), '/\\') . DIRECTORY_SEPARATOR . $newFileName;
    $this->assertFileExists($destinationPath);

    // The original temporary file should no longer exist.
    $this->assertFileDoesNotExist($tmpFile);

    unlink($destinationPath);
  }

  /**
   * @throws ServerException
   */
  public function testAddFailsWhenSanitizeUploadFails(): void {
    // Create a file meta array with an error (simulate UPLOAD_ERR_NO_FILE).
    $fileMeta = [
      'name' => 'nofile.txt',
      'full_path' => '',
      'type' => 'text/plain',
      'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
      'error' => UPLOAD_ERR_NO_FILE,
      'size' => 0,
    ];
    $upload = new FileUpload($fileMeta);

    $this->expectException(InvalidInputException::class);
    // Calling add() should trigger sanitizeUpload() and throw.
    $upload->add(1);

    if (file_exists($upload->getTmpName())) {
      unlink($upload->getTmpName());
    }
  }
}

