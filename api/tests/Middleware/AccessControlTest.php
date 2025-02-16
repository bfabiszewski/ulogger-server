<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Middleware;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use uLogger\Attribute\Route;
use uLogger\Component\Request;
use uLogger\Component\Response;
use uLogger\Component\Session;
use uLogger\Entity\Position;
use uLogger\Entity\Track;
use uLogger\Entity\User;
use uLogger\Mapper;
use uLogger\Mapper\MapperFactory;
use uLogger\Middleware\AccessControl;

class AccessControlTest extends TestCase {

  /** @var Session|MockObject */
  private Session|MockObject $sessionMock;

  /** @var Request|MockObject */
  private Request|MockObject $requestMock;

  /** @var Route|MockObject */
  private Route|MockObject $routeMock;

  /** @var MapperFactory|MockObject */
  private MapperFactory|MockObject $mapperFactoryMock;

  /** @var AccessControl */
  private AccessControl $accessControl;

  /**
   * @throws Exception
   */
  protected function setUp(): void {
    $this->sessionMock = $this->createMock(Session::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->routeMock = $this->createMock(Route::class);
    $this->mapperFactoryMock = $this->createMock(MapperFactory::class);

    $this->accessControl = new AccessControl($this->mapperFactoryMock, $this->sessionMock);
  }

  /**
   * Data provider for simple policies (ALLOW_ALL, ALLOW_AUTHORIZED, ALLOW_ADMIN).
   * For ALLOW_AUTHORIZED and ALLOW_ADMIN, the provider indicates whether the condition is met.
   *
   * Each entry is an associative array with keys:
   * - accessType: one of ACCESS_OPEN, ACCESS_PUBLIC, ACCESS_PRIVATE
   * - policy: one of ALLOW_ALL, ALLOW_AUTHORIZED, ALLOW_ADMIN
   * - isAuthenticated: (nullable) whether the session is authenticated (used for ALLOW_AUTHORIZED)
   * - isAdmin: (nullable) whether the session user has admin rights (used for ALLOW_ADMIN)
   * - isAllowed: expected result (true for allowed, false for not authorized)
   */
  public static function simplePolicyProvider(): array {
    return [
      // ALLOW_ALL: always allowed regardless of session state.
      [ 'accessType' => Session::ACCESS_OPEN, 'policy' => Session::ALLOW_ALL, 'isAuthenticated' => null, 'isAdmin' => null, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'policy' => Session::ALLOW_ALL, 'isAuthenticated' => null, 'isAdmin' => null, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'policy' => Session::ALLOW_ALL, 'isAuthenticated' => null, 'isAdmin' => null, 'isAllowed' => true ],
      // ALLOW_AUTHORIZED: allowed only if authenticated.
      [ 'accessType' => Session::ACCESS_OPEN, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => true, 'isAdmin' => null, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_OPEN, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => false, 'isAdmin' => null, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => true, 'isAdmin' => null, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => false, 'isAdmin' => null, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => true, 'isAdmin' => null, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'policy' => Session::ALLOW_AUTHORIZED, 'isAuthenticated' => false, 'isAdmin' => null, 'isAllowed' => false ],
      // ALLOW_ADMIN: allowed only if the session user is an administrator.
      [ 'accessType' => Session::ACCESS_OPEN, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_OPEN, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => false, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => false, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'policy' => Session::ALLOW_ADMIN, 'isAuthenticated' => null, 'isAdmin' => false, 'isAllowed' => false ],
    ];
  }

  /**
   * @dataProvider simplePolicyProvider
   */
  public function testSimplePolicies(string $accessType, string $policy, ?bool $isAuthenticated, ?bool $isAdmin, bool $isAllowed): void {
    // Configure the session's access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route auth configuration uses the same key as the session access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ $policy ]
    ]);

    // For ALLOW_AUTHORIZED, set whether the session is authenticated.
    if ($policy === Session::ALLOW_AUTHORIZED) {
      $this->sessionMock->method('isAuthenticated')->willReturn($isAuthenticated);
    }
    // For ALLOW_ADMIN, set whether the session user is an admin.
    if ($policy === Session::ALLOW_ADMIN) {
      $this->sessionMock->method('isAdmin')->willReturn($isAdmin);
    }

    // For ALLOW_ALL, no additional configuration is needed.
    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with policy $policy");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with policy $policy");
    }
  }

  /**
   * ACCESS_ALL is an alias for same set of polices being applied to all three access types.
   * So the result of ACCESS_ALL type should be same as any of the access types
   *
   * @dataProvider simplePolicyProvider
   */
  public function testAccessAllAlias(string $accessType, string $policy, ?bool $isAuthenticated, ?bool $isAdmin, bool $isAllowed): void {
    // Configure the session's access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route auth configuration uses ACCESS_ALL alias
    $this->routeMock->method('getAuth')->willReturn([
      Session::ACCESS_ALL => [ $policy ]
    ]);

    // For ALLOW_AUTHORIZED, set whether the session is authenticated.
    if ($policy === Session::ALLOW_AUTHORIZED) {
      $this->sessionMock->method('isAuthenticated')->willReturn($isAuthenticated);
    }
    // For ALLOW_ADMIN, set whether the session user is an admin.
    if ($policy === Session::ALLOW_ADMIN) {
      $this->sessionMock->method('isAdmin')->willReturn($isAdmin);
    }

    // For ALLOW_ALL, no additional configuration is needed.
    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with policy $policy");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with policy $policy");
    }
  }

  /**
   * Data provider for the ALLOW_OWNER policy.
   *
   * Each entry provides:
   * - accessType: one of ACCESS_OPEN, ACCESS_PUBLIC, ACCESS_PRIVATE
   * - ownerMatch: whether the session user owns the resource (true/false)
   * - isAllowed: expected outcome (true for allowed, false for denied)
   */
  public static function ownerPolicyProvider(): array {
    return [
      [ 'accessType' => Session::ACCESS_OPEN, 'ownerMatch' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_OPEN, 'ownerMatch' => false, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'ownerMatch' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PUBLIC, 'ownerMatch' => false, 'isAllowed' => false ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'ownerMatch' => true, 'isAllowed' => true ],
      [ 'accessType' => Session::ACCESS_PRIVATE, 'ownerMatch' => false, 'isAllowed' => false ],
    ];
  }

  /**
   * Test the ALLOW_OWNER policy for track resource.
   *
   * Simulate a route containing a '{trackId}' placeholder and use a mapper
   * to fetch the track. The mapper will return an entity with a userId that
   * either matches or does not match the session user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testTrackOwnerPolicy(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // Simulate a route with a {trackId} parameter.
    $trackId = 123;
    $this->routeMock->method('getPath')->willReturn('/tracks/{trackId}');
    $this->requestMock->method('getParams')->willReturn([ 'trackId' => (string) $trackId ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isAuthenticated')->willReturn(true);
    // Set a dummy session user.
    $userId = 42;
    $this->sessionMock->user = new User('test');
    $this->sessionMock->user->id = $userId;
    // Create a fake track entity. Its userId will match if $ownerMatch is true.
    $trackMock = new Track($ownerMatch ? $userId : $userId + 1, 'testTrack');

    // Create a stub for the track mapper.
    $trackMapperStub = $this->getMockBuilder(Mapper\Track::class)
      ->disableOriginalConstructor()
      ->onlyMethods([ 'fetch' ])
      ->getMock();
    $trackMapperStub->expects($this->once())
      ->method('fetch')
      ->with($trackId)
      ->willReturn($trackMock);

    $this->mapperFactoryMock->method('getMapper')
      ->with(Mapper\Track::class)
      ->willReturn($trackMapperStub);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  /**
   * Test the ALLOW_OWNER policy for user resource.
   *
   * Simulate a route containing a '{userId}' placeholder.
   * Session user should match route user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testUserOwnerPolicy(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // Simulate a route with a {userId} parameter.
    $userId = 42;
    $this->routeMock->method('getPath')->willReturn('/users/{userId}');
    $this->requestMock->method('getParams')->willReturn([ 'userId' => (string) $userId ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isSessionUser')
      ->with($userId)
      ->willReturn($ownerMatch);
    $this->sessionMock->method('isAuthenticated')
      ->willReturn(true);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  /**
   * Test the ALLOW_OWNER policy for position resource.
   *
   * Simulate a route containing a '{positionId}' placeholder.
   * Session user should match position user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testPositionOwnerPolicy(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // Simulate a route with a {positionId} parameter.
    $positionId = 123;
    $this->routeMock->method('getPath')->willReturn('/tracks/{positionId}');
    $this->requestMock->method('getParams')->willReturn([ 'positionId' => (string) $positionId ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isAuthenticated')->willReturn(true);
    // Set a dummy session user.
    $userId = 42;
    $this->sessionMock->user = new User('test');
    $this->sessionMock->user->id = $userId;
    // Create a fake track entity. Its userId will match if $ownerMatch is true.
    $positionMock = new Position(1, $ownerMatch ? $userId : $userId + 1, 1, 0, 0);

    // Create a stub for the track mapper.
    $positionMapperStub = $this->getMockBuilder(Mapper\Position::class)
      ->disableOriginalConstructor()
      ->onlyMethods([ 'fetch' ])
      ->getMock();
    $positionMapperStub->expects($this->once())
      ->method('fetch')
      ->with($positionId)
      ->willReturn($positionMock);

    $this->mapperFactoryMock->method('getMapper')
      ->with(Mapper\Position::class)
      ->willReturn($positionMapperStub);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  /**
   * Test the ALLOW_OWNER policy for track resource.
   *
   * Simulate a route containing track payload.
   * Session user should match track user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testNewTrackOwnerPolicy(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isAuthenticated')->willReturn(true);
    // Set a dummy session user.
    $userId = 42;
    // Create a fake track entity. Its userId will match if $ownerMatch is true.
    $trackMock = new Track($ownerMatch ? $userId : $userId + 1, 'testTrack');

    $this->sessionMock->user = new User('test');
    $this->sessionMock->user->id = $userId;

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isSessionUser')
      ->willReturn($ownerMatch);

    $this->requestMock->method('hasPayload')
      ->willReturn(true);
    $this->requestMock->method('hasPreparedArgument')
      ->willReturnCallback(function ($className) {
        return $className === Track::class;
      });
    $this->requestMock->method('getPreparedArgument')
      ->with(Track::class)
      ->willReturn($trackMock);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  /**
   * Test the ALLOW_OWNER policy for position resource.
   *
   * Simulate a route containing position payload.
   * Session user should match position user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testNewPositionOwnerPolicy(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isAuthenticated')->willReturn(true);
    // Set a dummy session user.
    $userId = 42;
    $this->sessionMock->user = new User('test');
    $this->sessionMock->user->id = $userId;
    $trackId = 123;
    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isSessionUser')
      ->willReturn($ownerMatch);
    // Create a fake position entity. Its userId will match if $ownerMatch is true.
    $positionMock = new Position(1, $ownerMatch ? $userId : $userId + 1, $trackId, 0, 0);

    $this->requestMock->method('hasPayload')
      ->willReturn(true);
    $this->requestMock->method('hasPreparedArgument')
      ->willReturnCallback(function ($className) {
        return $className === Position::class;
      });
    $this->requestMock->method('getPreparedArgument')
      ->with(Position::class)
      ->willReturn($positionMock);

    // Create a fake track entity. Its userId will match if $ownerMatch is true.
    $trackMock = new Track($ownerMatch ? $userId : $userId + 1, 'testTrack');

    // Create a stub for the track mapper.
    $trackMapperStub = $this->getMockBuilder(Mapper\Track::class)
      ->disableOriginalConstructor()
      ->onlyMethods([ 'fetch' ])
      ->getMock();
    $trackMapperStub
      ->method('fetch')
      ->with($trackId)
      ->willReturn($trackMock);
    $this->mapperFactoryMock->method('getMapper')
      ->with(Mapper\Track::class)
      ->willReturn($trackMapperStub);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  /**
   * Test the ALLOW_OWNER policy for position resource.
   *
   * Simulate a route containing position payload.
   * Session user should match position user.
   *
   * @dataProvider ownerPolicyProvider
   */
  public function testNewPositionOwnerPolicyWithWrongTrack(string $accessType, bool $ownerMatch, bool $isAllowed): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn($accessType);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      $accessType => [ Session::ALLOW_OWNER ]
    ]);

    // The resource owner check requires the session to be authenticated.
    $this->sessionMock->method('isAuthenticated')->willReturn(true);
    // Set a dummy session user.
    $userId = 42;
    $this->sessionMock->user = new User('test');
    $this->sessionMock->user->id = $userId;
    $trackId = 123;
    // The resource owner check requires the session to be authenticated.
    // Always pass this check, so that later track check may fail.
    $this->sessionMock->method('isSessionUser')
      ->willReturn(true);
    // Create a fake position entity. Its userId will match if $ownerMatch is true.
    $positionMock = new Position(1, $ownerMatch ? $userId : $userId + 1, $trackId, 0, 0);

    $this->requestMock->method('hasPayload')
      ->willReturn(true);
    $this->requestMock->method('hasPreparedArgument')
      ->willReturnCallback(function ($className) {
        return $className === Position::class;
      });
    $this->requestMock->method('getPreparedArgument')
      ->with(Position::class)
      ->willReturn($positionMock);

    // Create a fake track entity. Its userId will match if $ownerMatch is true.
    $trackMock = new Track($ownerMatch ? $userId : $userId + 1, 'testTrack');

    // Create a stub for the track mapper.
    $trackMapperStub = $this->getMockBuilder(Mapper\Track::class)
      ->disableOriginalConstructor()
      ->onlyMethods([ 'fetch' ])
      ->getMock();
    $trackMapperStub
      ->method('fetch')
      ->with($trackId)
      ->willReturn($trackMock);
    $this->mapperFactoryMock->method('getMapper')
      ->with(Mapper\Track::class)
      ->willReturn($trackMapperStub);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    if ($isAllowed) {
      $this->assertEquals(Response::continue(), $response, "Expected allowed for $accessType with ALLOW_OWNER when ownerMatch is true");
    } else {
      $this->assertEquals(Response::notAuthorized(), $response, "Expected not authorized for $accessType with ALLOW_OWNER when ownerMatch is false");
    }
  }

  public function testUserOwnerPolicyFailsWhenNoUserAuthenticated(): void {
    // Set the session access type.
    $this->sessionMock->method('getAccessType')->willReturn(Session::ACCESS_PUBLIC);
    // The route requires ALLOW_OWNER for the given access type.
    $this->routeMock->method('getAuth')->willReturn([
      Session::ACCESS_PUBLIC => [ Session::ALLOW_OWNER ]
    ]);

    $this->sessionMock->method('isAuthenticated')
      ->willReturn(false);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    $this->assertEquals(Response::notAuthorized(), $response, 'Expected not authorized');
  }

  public function testInvalidPolicyConfiguration(): void {
    // Session's access type is ACCESS_PUBLIC but the route auth is empty.
    $this->sessionMock->method('getAccessType')->willReturn(Session::ACCESS_PUBLIC);
    $this->routeMock->method('getAuth')->willReturn([]);

    $response = $this->accessControl->run($this->requestMock, $this->routeMock);
    $this->assertStringContainsString('No policies found for route', $response->getPayload()['message']);
  }
}

