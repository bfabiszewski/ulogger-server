<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Component;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use uLogger\Component\Router;
use uLogger\Component\Request;
use uLogger\Component\Response;
use uLogger\Attribute\Route;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;
use uLogger\Mapper\MapperFactory;
use uLogger\Component\Session;
use uLogger\Entity\Config;
use uLogger\Middleware\MiddlewareInterface;
use InvalidArgumentException;
use ReflectionClass;

class RouterTest extends TestCase {
  /** @var Router */
  private Router $router;

  /** @var MockObject|Request */
  private MockObject|Request $requestMock;

  /** @var MockObject|Session */
  private MockObject|Session $sessionMock;

  /** @var MockObject|Config */
  private MockObject|Config $configMock;

  /** @var MockObject|MapperFactory */
  private MockObject|MapperFactory $mapperFactoryMock;

  /**
   * @throws Exception
   */
  protected function setUp(): void {
    $this->router = new Router();
    $this->requestMock = $this->createMock(Request::class);
    $this->sessionMock = $this->createMock(Session::class);
    $this->configMock = $this->createMock(Config::class);
    $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
  }

  /**
   * Test that setupRoutes populates the router with route definitions.
   * @throws ServerException
   */
  public function testSetupRoutesAddsRoutes(): void {

    $this->router->setupRoutes($this->sessionMock, $this->configMock, $this->mapperFactoryMock);

    $routes = $this->getRoutes();

    // Assert that routes have been added.
    $this->assertNotEmpty($routes, 'Routes should not be empty after setupRoutes is called.');
  }

  /**
   * When the URI segments do not indicate an api or client request,
   * dispatch() should return a notFound response.
   * @throws InvalidInputException
   * @throws ServerException
   * @throws ReflectionException
   */
  public function testDispatchNotApiOrClient(): void {

    $this->requestMock->method('getUriSegments')
      ->willReturn([ '', 'notapi', '' ]);

    $response = $this->router->dispatch($this->requestMock);
    $this->assertEquals(Response::notFound(), $response);
  }

  /**
   * When there is no matching route for the request method,
   * dispatch() should return a notFound response.
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   */
  public function testDispatchRouteNotFound(): void {

    $this->requestMock->method('getUriSegments')
      ->willReturn([ '', 'api', 'something' ]);

    $this->requestMock->method('getMethod')
      ->willReturn('GET');

    // Since no routes have been added, dispatch should return notFound.
    $response = $this->router->dispatch($this->requestMock);
    $this->assertEquals(Response::notFound(), $response);
  }

  /**
   * Test a successful dispatch where a route matches, middleware passes,
   * and the handler is successfully invoked.
   *
   * This test manually adds a route to the router using reflection to
   * bypass the private addRoute() method.
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   */
  public function testDispatchWithMatchingRouteAndMiddlewareContinue(): void {

    $path = '/api/test';
    $method = 'GET';

    $request = new Request($path, $method);

    $expectedResponse = Response::success('test');

    $handler = new class ($expectedResponse) {
      private Response $expectedResponse;
      public function __construct(Response $expectedResponse) {
        $this->expectedResponse = $expectedResponse;
      }
      public function test(): Response {
        return $this->expectedResponse;
      }
    };

    $route = new Route($method, $path, [], [ $handler, 'test' ]);
    $this->addRoute($route);

    // Add a middleware that returns a "continue" response.
    $middleware = new class implements MiddlewareInterface {
      // @codingStandardsIgnoreLine
      public function run(Request $request, Route $route): Response {
        return Response::continue();
      }
    };
    $this->router->addMiddleware($middleware);

    $response = $this->router->dispatch($request);
    $this->assertEquals($expectedResponse, $response);
  }

  /**
   * Test that if a middleware returns a response other than "continue",
   * the dispatch method returns that middleware response.
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   */
  public function testDispatchWithMiddlewareInterrupt(): void {

    $path = '/api/test';
    $method = 'GET';

    $request = new Request($path, $method);

    $expectedResponse = Response::notAuthorized();

    $handler = new class {
      public function test(): Response {
        return Response::success('test');
      }
    };

    $route = new Route($method, $path, [], [ $handler, 'test' ]);
    $this->addRoute($route);

    // Add a middleware that returns a "not authorized" response.
    $middleware = new class ($expectedResponse) implements MiddlewareInterface {
      private Response $expectedResponse;
      public function __construct(Response $expectedResponse) {
        $this->expectedResponse = $expectedResponse;
      }
      // @codingStandardsIgnoreLine
      public function run(Request $request, Route $route): Response {
        return $this->expectedResponse;
      }
    };
    $this->router->addMiddleware($middleware);

    $response = $this->router->dispatch($request);
    $this->assertEquals($expectedResponse, $response);
  }

  /**
   * Test the callHandler() method with a valid callable.
   *
   * Since callHandler() is private, we invoke it using reflection.
   * @throws ReflectionException
   */
  public function testCallHandlerWithValidHandler(): void {
    $expectedResponse = Response::continue();
    $handler = function () use ($expectedResponse) {
      return $expectedResponse;
    };

    $routerReflection = new ReflectionClass($this->router);
    $method = $routerReflection->getMethod('callHandler');

    // Simulate that the Request's prepared arguments are an empty array.
    $this->requestMock->method('getPreparedArguments')
      ->willReturn([]);

    $requestProp = $routerReflection->getProperty('request');
    $requestProp->setValue($this->router, $this->requestMock);

    $actualResponse = $method->invoke($this->router, $handler);
    $this->assertEquals($expectedResponse, $actualResponse);
  }

  /**
   * Test that callHandler() throws an exception when the handler is not callable.
   * @throws ReflectionException
   */
  public function testCallHandlerWithInvalidHandler(): void {
    $invalidHandler = [];

    $routerReflection = new ReflectionClass($this->router);
    $method = $routerReflection->getMethod('callHandler');

    $requestProp = $routerReflection->getProperty('request');
    $requestProp->setValue($this->router, $this->requestMock);

    $this->expectException(InvalidArgumentException::class);
    $method->invoke($this->router, $invalidHandler);
  }

  /**
   * Add this route to the router's routes.
   * @param Route $route
   * @return void
   */
  private function addRoute(Route $route): void {
    $routerReflection = new ReflectionClass($this->router);
    $routesProp = $routerReflection->getProperty('routes');
    $routes = $routesProp->getValue($this->router);
    $routes[$route->getMethod()][$route->getPath()] = $route;
    $routesProp->setValue($this->router, $routes);
  }

  /**
   * @return Route[]
   */
  private function getRoutes(): array {
    $reflection = new ReflectionClass($this->router);
    $routesProp = $reflection->getProperty('routes');
    return $routesProp->getValue($this->router);
  }
}
