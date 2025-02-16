<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Component;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use uLogger\Component\FileUpload;
use uLogger\Component\Request;
use uLogger\Entity\Track;
use uLogger\Entity\User;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;

final class RequestTest extends TestCase {

  public function testGetUriSegments(): void {
    $path = '/a/b/c';
    $request = new Request($path, 'GET');
    $segments = $request->getUriSegments();
    $this->assertEquals([ '', 'a', 'b', 'c' ], $segments);
  }

  public function testGetMethod(): void {
    $method = 'POST';
    $request = new Request('/test', $method);
    $this->assertEquals($method, $request->getMethod());
  }

  public function testMatchPathSuccess(): void {
    $id = '123';
    $path = "/api/users/$id";
    $request = new Request($path, 'GET');
    $result = $request->matchPath('/api/users/{id}');
    $this->assertTrue($result, 'Expected route to match the request path');
    $params = $request->getParams();
    $this->assertArrayHasKey('id', $params);
    $this->assertEquals($id, $params['id']);
  }

  public function testMatchPathFailure(): void {
    $path = '/api/users/123';
    $request = new Request($path, 'GET');
    $result = $request->matchPath('/api/orders/{id}');
    $this->assertFalse($result, 'Expected route not to match the request path');
  }

  public function testHasPayload(): void {
    $payload = [ 'key' => 'value' ];
    $request = new Request('/test', 'POST', $payload);
    $this->assertTrue($request->hasPayload(), 'Expected payload to be detected as non-empty');

    $requestEmpty = new Request('/test', 'POST', []);
    $this->assertFalse($requestEmpty->hasPayload(), 'Expected payload to be detected as empty');
  }

  /**
   * @throws Exception
   */
  public function testGetUploadAndHasUpload(): void {
    $fileUploadMock = $this->createMock(FileUpload::class);
    $uploads = [ 'file' => $fileUploadMock ];
    $request = new Request('/upload', 'POST', [], $uploads);
    $this->assertTrue($request->hasUpload('file'), "Expected upload 'file' to exist");
    $this->assertSame($fileUploadMock, $request->getUpload('file'));
    $this->assertFalse($request->hasUpload('nonexistent'), "Expected upload 'nonexistent' not to exist");
    $this->assertNull($request->getUpload('nonexistent'));
  }

  /**
   * Test that parseHandlerArguments() correctly prepares arguments when a route
   * parameter is available. Here handler expects an int.
   */
  public function testParseHandlerArgumentsWithParam(): void {
    $handler = new class {
      public function test(int $id): int {
        return $id;
      }
    };
    // Create a request with a path that will extract the "id" parameter.
    $id = 123;
    $request = new Request("/dummy/$id", 'GET', [], [], []);
    $request->matchPath('/dummy/{id}');

    try {
      $request->parseHandlerArguments([ $handler, 'test' ]);
    } catch (ServerException|InvalidInputException|ReflectionException $e) {
      $this->fail('parseHandlerArguments threw an exception: ' . $e->getMessage());
    }
    $prepared = $request->getPreparedArguments();
    $this->assertCount(1, $prepared, 'Expected one prepared argument');
    $this->assertSame($id, $prepared[0]);
  }

  /**
   * @throws Exception
   */
  public function testParseHandlerArgumentsWithFileUpload(): void {
    $handler = new class {
      public function test(FileUpload $upload): FileUpload {
        return $upload;
      }
    };

    $fileUploadMock = $this->createMock(FileUpload::class);
    $request = new Request('/dummy', 'POST', [], [ 'upload' => $fileUploadMock ], []);
    $request->matchPath('/dummy');

    try {
      $request->parseHandlerArguments([ $handler, 'test' ]);
    } catch (ServerException|InvalidInputException|ReflectionException $e) {
      $this->fail('parseHandlerArguments threw an exception: ' . $e->getMessage());
    }
    $prepared = $request->getPreparedArguments();
    $this->assertCount(1, $prepared, 'Expected one prepared argument');
    $this->assertSame($fileUploadMock, $prepared[0]);
  }

  /**
   * @throws Exception
   */
  public function testParseHandlerArgumentsWithVariadicArgumentAndFileUpload(): void {
    $handler = new class {
      public function test(mixed ...$params): array {
        return $params;
      }
    };

    $fileUploadMock = $this->createMock(FileUpload::class);
    $payload = [ 'arg' => 'test' ];
    $request = new Request('/dummy', 'POST', $payload, [ 'upload' => $fileUploadMock ], []);
    $request->matchPath('/dummy');

    try {
      $request->parseHandlerArguments([ $handler, 'test' ]);
    } catch (ServerException|InvalidInputException|ReflectionException $e) {
      $this->fail('parseHandlerArguments threw an exception: ' . $e->getMessage());
    }
    $prepared = $request->getPreparedArguments();
    $this->assertCount(2, $prepared, 'Expected two prepared arguments');
    $this->assertSame($payload['arg'], $prepared['arg']);
    $this->assertSame($fileUploadMock, $prepared['upload']);
  }

  /**
   * Test that parseHandlerArguments() throws an exception if a required parameter
   * is missing.
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   */
  public function testParseHandlerArgumentsMissingParameter(): void {
    $handler = new class {
      public function test(string $foo): string {
        return $foo;
      }
    };
    $request = new Request('/dummy', 'GET', [], [], []);
    $request->matchPath('/dummy');
    $this->expectException(InvalidInputException::class);
    $request->parseHandlerArguments([ $handler, 'test' ]);
  }

  public function testParseHandlerArgumentsWithEntity(): void {
    $handler = new class {
      public function test(Track $entity): Track {
        return $entity;
      }
    };
    $payload = [ 'userId' => 1, 'name' => 'testName' ];
    $request = new Request('/dummy', 'POST', $payload, [], []);
    $request->matchPath('/dummy');
    // Call parseHandlerArguments for a method expecting an entity.
    try {
      $request->parseHandlerArguments([ $handler, 'test' ]);
    } catch (ServerException|InvalidInputException|ReflectionException $e) {
      $this->fail('parseHandlerArguments with entity threw exception: ' . $e->getMessage());
    }
    $prepared = $request->getPreparedArguments();
    $this->assertCount(1, $prepared, 'Expected one prepared argument');
    $this->assertInstanceOf(Track::class, $prepared[0]);
    $this->assertEquals($payload['userId'], $prepared[0]->userId);
    $this->assertEquals($payload['name'], $prepared[0]->name);
  }

  public function testParseHandlerArgumentsWithPayload(): void {
    $handler = new class {
      public function test(string $arg): string {
        return $arg;
      }
    };
    $payload = [ 'arg' => 'payload' ];
    $request = new Request('/dummy', 'POST', $payload, [], []);
    $request->matchPath('/dummy');
    // Call parseHandlerArguments for a method expecting a payload.
    try {
      $request->parseHandlerArguments([ $handler, 'test' ]);
    } catch (ServerException|InvalidInputException|ReflectionException $e) {
      $this->fail('parseHandlerArguments with entity threw exception: ' . $e->getMessage());
    }
    $prepared = $request->getPreparedArguments();
    $this->assertCount(1, $prepared, 'Expected one prepared argument');
    $this->assertEquals($payload['arg'], $prepared[0]);
  }

  /**
   * @throws Exception
   */
  public function testHasPreparedArgumentAndGetPreparedArgument(): void {
    $mockUser = $this->createMock(User::class);
    $request = new Request('/test', 'GET');
    $this->setPreparedArguments($request, $mockUser);

    $this->assertTrue($request->hasPreparedArgument(User::class));
    $this->assertSame($mockUser, $request->getPreparedArgument(User::class));

    $this->assertFalse($request->hasPreparedArgument(Track::class));
    $this->assertNull($request->getPreparedArgument(Track::class));
  }

  /**
   * @param Request $request
   * @param mixed $argument
   */
  private function setPreparedArguments(Request $request, mixed $argument): void {
    $refClass = new ReflectionClass($request);
    $prop = $refClass->getProperty('preparedArguments');
    $prop->setValue($request, [ $argument ]);
  }
}
