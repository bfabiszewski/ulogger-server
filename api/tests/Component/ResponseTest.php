<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Component;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use uLogger\Component\Response;
use uLogger\Component\Request;
use uLogger\Entity\File;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\GpxParseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;

final class ResponseTest extends TestCase {
  public function testConstructorAndGetters(): void {
    $payload = 'Test data';
    $code = Response::CODE_2_OK;
    $contentType = Response::TYPE_JSON;
    $response = new Response($payload, $code, $contentType);

    $this->assertSame($payload, $response->getPayload());
    $this->assertSame($code, $response->getCode());
    $this->assertSame($contentType, $response->getContentType());
    $this->assertEmpty($response->getExtraHeaders());
  }

  public function testStaticError(): void {
    $message = 'Error occurred';
    $code = Response::CODE_5_INTERNAL;
    $response = Response::error($message, $code);

    $this->assertSame($code, $response->getCode());
    $payload = $response->getPayload();
    $this->assertIsArray($payload);
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testStaticFieldsError(): void {
    $fields = [ 'username' => 'required' ];
    $code = Response::CODE_4_UNPROCESSABLE;
    $response = Response::fieldsError($fields, $code);

    $this->assertSame($code, $response->getCode());
    $this->assertEquals([ 'fields' => $fields ], $response->getPayload());
  }

  public function testStaticSuccessWithPayload(): void {
    $data = [ 'foo' => 'bar' ];
    $code = Response::CODE_2_OK;
    $response = Response::success($data, $code);

    $this->assertSame($code, $response->getCode());
    $this->assertSame($data, $response->getPayload());
    $this->assertSame(Response::TYPE_JSON, $response->getContentType());
  }

  public function testStaticSuccessWithoutPayload(): void {
    $response = Response::success();
    $this->assertSame(Response::CODE_2_NOCONTENT, $response->getCode());
  }

  /**
   * @throws \PHPUnit\Framework\MockObject\Exception
   */
  public function testFileAndFileAttachment(): void {
    $content = 'file content';
    $mimeType = 'text/plain';
    $fileName = 'test.txt';

    $mockFile = $this->createMock(File::class);
    $mockFile->method('getContent')->willReturn($content);
    $mockFile->method('getMimeType')->willReturn($mimeType);
    $mockFile->method('getFileName')->willReturn($fileName);

    // Test Response::file()
    $fileResponse = Response::file($mockFile);
    $this->assertSame($content, $fileResponse->getPayload());
    $this->assertSame(Response::CODE_2_OK, $fileResponse->getCode());
    $this->assertSame($mimeType, $fileResponse->getContentType());

    // Test Response::fileAttachment()
    $attachmentResponse = Response::fileAttachment($mockFile);
    $headers = $attachmentResponse->getExtraHeaders();
    $expectedDisposition = "attachment; filename=\"$fileName\"";
    $this->assertArrayHasKey(Request::CONTENT_DISPOSITION, $headers);
    $this->assertSame($expectedDisposition, $headers[Request::CONTENT_DISPOSITION]);
  }

  public function testInternalServerError(): void {
    $message = 'Internal error';
    $response = Response::internalServerError($message);

    $this->assertSame(Response::CODE_5_INTERNAL, $response->getCode());
    $payload = $response->getPayload();
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testDatabaseError(): void {
    $message = 'DB failure';
    $response = Response::databaseError($message);
    $this->assertSame(Response::CODE_5_INTERNAL, $response->getCode());
    $payload = $response->getPayload();
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testUnprocessableError(): void {
    $message = 'Data invalid';
    $response = Response::unprocessableError($message);
    $this->assertSame(Response::CODE_4_UNPROCESSABLE, $response->getCode());
    $payload = $response->getPayload();
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testConflictError(): void {
    $message = 'Conflict occurred';
    $response = Response::conflictError($message);
    $this->assertSame(Response::CODE_4_CONFLICT, $response->getCode());
    $payload = $response->getPayload();
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testCreated(): void {
    $data = [ 'created' => true ];
    $response = Response::created($data);
    $this->assertSame(Response::CODE_2_CREATED, $response->getCode());
    $this->assertSame($data, $response->getPayload());
    $this->assertSame(Response::TYPE_JSON, $response->getContentType());
  }

  public function testNotFound(): void {
    $response = Response::notFound();
    $this->assertSame(Response::CODE_4_NOTFOUND, $response->getCode());
  }

  public function testRedirect(): void {
    $path = '/login';
    $response = Response::redirect($path);
    $this->assertSame(Response::CODE_3_FOUND, $response->getCode());
    $this->assertSame('', $response->getContentType());
    $headers = $response->getExtraHeaders();
    $this->assertArrayHasKey('Location', $headers);
    $this->assertSame($path, $headers['Location']);
  }

  public function testNotAuthorized(): void {
    $response = Response::notAuthorized();
    $this->assertSame(Response::CODE_4_UNAUTHORIZED, $response->getCode());
  }

  public function testContinue(): void {
    $response = Response::continue();
    $this->assertSame(Response::CODE_1_CONTINUE, $response->getCode());
  }

  public function testForbidden(): void {
    $message = 'Access denied';
    $response = Response::forbidden($message);
    $this->assertSame(Response::CODE_4_FORBIDDEN, $response->getCode());
    $payload = $response->getPayload();
    $this->assertTrue($payload['error']);
    $this->assertSame($message, $payload['message']);
  }

  public function testExceptionMethod(): void {
    // For DatabaseException
    $dbException = new DatabaseException('DB error');
    $response = Response::exception($dbException);
    $this->assertSame(Response::CODE_5_INTERNAL, $response->getCode());
    $this->assertSame('DB error', $response->getPayload()['message']);

    // For ServerException
    $serverException = new ServerException('Server error');
    $response = Response::exception($serverException);
    $this->assertSame(Response::CODE_5_INTERNAL, $response->getCode());
    $this->assertSame('Server error', $response->getPayload()['message']);

    // For InvalidInputException
    $invalidInput = new InvalidInputException('Invalid input');
    $response = Response::exception($invalidInput);
    $this->assertSame(Response::CODE_4_UNPROCESSABLE, $response->getCode());
    $this->assertSame('Invalid input', $response->getPayload()['message']);

    // For GpxParseException
    $gpxException = new GpxParseException('GPX parse error');
    $response = Response::exception($gpxException);
    $this->assertSame(Response::CODE_4_UNPROCESSABLE, $response->getCode());
    $this->assertSame('GPX parse error', $response->getPayload()['message']);

    // For NotFoundException
    $notFound = new NotFoundException('Not found');
    $response = Response::exception($notFound);
    $this->assertSame(Response::CODE_4_NOTFOUND, $response->getCode());

    // For generic Exception
    $genericException = new Exception('Some error');
    $response = Response::exception($genericException);
    $this->assertSame(Response::CODE_5_INTERNAL, $response->getCode());
    $this->assertSame('An unexpected error occurred.', $response->getPayload()['message']);
  }

  /**
   * @throws JsonException
   */
  public function testSendJsonOutput(): void {
    $payload = [ 'test' => 'value' ];
    $response = Response::success($payload);
    // Start output buffering to capture echo from send()
    ob_start();
    $response->send();
    $output = ob_get_clean();
    $sentHeaders = php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();

    $expectedOutput = json_encode($payload, JSON_THROW_ON_ERROR);
    $this->assertSame($expectedOutput, $output);
    $this->assertHeadersContains('Content-Type: application/json', $sentHeaders);
  }

  public function testSendNonJsonOutput(): void {
    $payload = 'Plain text response';
    $response = new Response($payload, Response::CODE_2_OK, 'text/plain');
    ob_start();
    $response->send();
    $output = ob_get_clean();
    $sentHeaders = php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();

    $this->assertSame($payload, $output);
    $this->assertHeadersContains('Content-Type: text/plain', $sentHeaders);
  }

  /**
   * @throws \PHPUnit\Framework\MockObject\Exception
   */
  public function testSendFileAttachment(): void {
    $content = 'file content';
    $mimeType = 'text/plain';
    $fileName = 'test.txt';

    $mockFile = $this->createMock(File::class);
    $mockFile->method('getContent')->willReturn($content);
    $mockFile->method('getMimeType')->willReturn($mimeType);
    $mockFile->method('getFileName')->willReturn($fileName);

    $response = Response::fileAttachment($mockFile);
    ob_start();
    $response->send();
    $output = ob_get_clean();
    $sentHeaders = php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();

    $this->assertSame($content, $output);
    $this->assertCount(3, $sentHeaders);
    $this->assertHeadersContains("Content-Disposition: attachment; filename=\"$fileName\"", $sentHeaders);
    $this->assertHeadersContains('Content-Type: text/plain', $sentHeaders);
  }

  public function testSetContentType(): void {
    $response = new Response('data');
    $response->setContentType('text/html');
    $this->assertSame('text/html', $response->getContentType());
  }

  private function assertHeadersContains(string $expectedHeader, array $headers): void {
    foreach ($headers as $header) {
      if (stristr($header, $expectedHeader)) {
        return;
      }
    }
    $this->fail();
  }
}
