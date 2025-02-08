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
use ReflectionClass;
use ReflectionException;
use uLogger\Component\Session;
use uLogger\Entity\Config;
use uLogger\Entity\User;
use uLogger\Mapper\MapperFactory;
use uLogger\Mapper;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\InvalidInputException;

final class SessionTest extends TestCase {
  /** @var MapperFactory|MockObject */
  private MapperFactory|MockObject $mapperFactoryMock;

  /** @var Mapper\User|MockObject */
  private Mapper\User|MockObject $userMapperMock;

  /** @var Session */
  private Session $session;

  /**
   * @throws ServerException
   * @throws Exception
   */
  protected function setUp(): void {
    $this->userMapperMock = $this->createMock(Mapper\User::class);
    $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
    $this->mapperFactoryMock->method('getMapper')
      ->with(Mapper\User::class)
      ->willReturn($this->userMapperMock);

    $config = new Config();
    $config->requireAuthentication = true;
    $config->publicTracks = false;
    $this->session = new Session($this->mapperFactoryMock, $config);
  }

  /**
   * @throws ServerException
   */
  public function testGetAccessTypeOpen(): void {
    // When authentication is not required, access should be ACCESS_OPEN.
    $config = new Config();
    $config->requireAuthentication = false;
    $config->publicTracks = false;
    $session = new Session($this->mapperFactoryMock, $config);
    $this->assertSame(Session::ACCESS_OPEN, $session->getAccessType());
  }

  /**
   * @throws ServerException
   */
  public function testGetAccessTypePublic(): void {
    // When authentication is required and publicTracks is enabled, access should be ACCESS_PUBLIC.
    $config = new Config();
    $config->requireAuthentication = true;
    $config->publicTracks = true;
    $session = new Session($this->mapperFactoryMock, $config);
    $this->assertSame(Session::ACCESS_PUBLIC, $session->getAccessType());
  }

  /**
   * @throws ServerException
   */
  public function testGetAccessTypePrivate(): void {
    // When authentication is required and publicTracks is disabled, access should be ACCESS_PRIVATE.
    $config = new Config();
    $config->requireAuthentication = true;
    $config->publicTracks = false;
    $session = new Session($this->mapperFactoryMock, $config);
    $this->assertSame(Session::ACCESS_PRIVATE, $session->getAccessType());
  }

  /**
   * @throws InvalidInputException
   */
  public function testUpdateSessionNotAuthenticatedDoesNotCallStoreInSession(): void {
    // When the session is not authenticated, updateSession should not call storeInSession.
    $this->userMapperMock->expects($this->never())->method('storeInSession');
    $this->session->updateSession();
  }

  /**
   * @throws ReflectionException
   * @throws InvalidInputException
   */
  public function testUpdateSessionAuthenticatedCallsStoreInSession(): void {
    // Simulate authentication by calling the private setAuthenticated() method via reflection.
    $dummyUser = new User('testUser');
    $dummyUser->id = 1;
    $dummyUser->isAdmin = false;
    $refClass = new ReflectionClass($this->session);
    $setAuthMethod = $refClass->getMethod('setAuthenticated');
    $setAuthMethod->invoke($this->session, $dummyUser);

    // Now, updateSession() should call storeInSession with the authenticated user.
    $this->userMapperMock->expects($this->once())
      ->method('storeInSession')
      ->with($dummyUser);
    $this->session->updateSession();
  }

  /**
   * @throws ReflectionException
   */
  public function testIsAuthenticatedIsAdminAndIsSessionUser(): void {
    // Initially, the session is not authenticated.
    $this->assertFalse($this->session->isAuthenticated());

    // Simulate authentication.
    $dummyUser = new User('testUser');
    $userId = 42;
    $dummyUser->id = $userId;
    $dummyUser->isAdmin = true;
    $refClass = new ReflectionClass($this->session);
    $setAuthMethod = $refClass->getMethod('setAuthenticated');
    $setAuthMethod->invoke($this->session, $dummyUser);

    $this->assertTrue($this->session->isAuthenticated());
    $this->assertTrue($this->session->isAdmin());
    $this->assertTrue($this->session->isSessionUser($userId));
  }

  /**
   * @runInSeparateProcess
   * @throws DatabaseException|ServerException
   */
  public function testInitSetsAuthenticatedWhenUserFound(): void {
    $dummyUser = new User('test');
    $dummyUser->id = 5;
    $dummyUser->isAdmin = false;
    $this->userMapperMock->method('getFromSession')
      ->willReturn(5);
    $this->userMapperMock->method('fetch')
      ->with(5)
      ->willReturn($dummyUser);

    $this->session->init();

    $this->assertTrue($this->session->isAuthenticated());
    $this->assertSame($dummyUser, $this->session->user);
  }

  /**
   * @throws NotFoundException
   * @throws DatabaseException
   * @throws ServerException
   * @throws InvalidInputException
   */
  public function testSetAuthenticatedIfValidWithValidPassword(): void {
    $login = 'testUser';
    $password = 'correct';
    $dummyUser = new User($login);
    $dummyUser->id = 10;
    $dummyUser->isAdmin = false;
    $dummyUser->hash = password_hash($password, PASSWORD_DEFAULT);

    $this->userMapperMock->expects($this->once())
      ->method('fetchByLogin')
      ->with($login)
      ->willReturn($dummyUser);

    $this->userMapperMock->expects($this->once())
      ->method('storeInSession')
      ->with($dummyUser);

    $this->session->setAuthenticatedIfValid($login, $password);
    $this->assertTrue($this->session->isAuthenticated());
    $this->assertSame($dummyUser, $this->session->user);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws InvalidInputException
   */
  public function testSetAuthenticatedIfValidWithInvalidPassword(): void {
    $login = 'testUser';
    $password = 'wrong';
    $dummyUser = new User($login);
    $dummyUser->id = 10;
    $dummyUser->isAdmin = false;

    $this->userMapperMock->expects($this->once())
      ->method('fetchByLogin')
      ->with($login)
      ->willReturn($dummyUser);

    $this->expectException(NotFoundException::class);
    $this->session->setAuthenticatedIfValid($login, $password);
  }

  /**
   * @runInSeparateProcess
   */
  public function testLogOutEndsSession(): void {
    // Start a session and set a dummy session variable.
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $_SESSION['test'] = 'value';
    // Call logOut(), which should clean the session variables and destroy the session.
    $this->session->logOut();
    $this->assertEmpty($_SESSION, 'Expected session to be empty after logOut()');
  }
}
