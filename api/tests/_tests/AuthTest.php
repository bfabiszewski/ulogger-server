<?php
declare(strict_types = 1);

namespace uLogger\Tests\_tests;

use uLogger\Component\Session;
use uLogger\Entity\User;
use uLogger\Tests\_lib\UloggerDatabaseTestCase;

class AuthTest extends UloggerDatabaseTestCase {

  public function setUp(): void {
    $_SESSION = [];
    parent::setUp();
  }

  /**
   * @runInSeparateProcess
   */
  public function testLogin(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new Session();
    $auth->setAuthenticatedIfValid($this->testUser, $this->testPass);
    self::assertTrue($auth->isAuthenticated(), "Not authenticated");
    self::assertInstanceOf(User::class, $auth->user, "User variable not set");
    self::assertEquals($this->testUser, $auth->user->login, "Wrong login");
    self::assertEquals($_SESSION["user"]->login, $auth->user->login, "Wrong login");
    self::assertInstanceOf(User::class, $_SESSION["user"], "User not set in session");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginBadPass(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new Session();
    $auth->setAuthenticatedIfValid($this->testUser, "badPass");
    self::assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    self::assertNull($auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginEmptyLogin(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new Session();
    $auth->setAuthenticatedIfValid("", $this->testPass);
    self::assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    self::assertNull($auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginNoFormData(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new Session();
    self::assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    self::assertNull($auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testSessionAuth(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new User($this->testUser);
    self::assertTrue($user->isValid, "User not valid");
    session_name("ulogger");
    session_start();
    $_SESSION["user"] = $user;
    unset($user);

    @$auth = new Session();
    self::assertTrue($auth->isAuthenticated(), "Should be authenticated");
    self::assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }

  /**
   * @runInSeparateProcess
   */
  public function testSessionAndRequest(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new User($this->testUser);
    self::assertTrue($user->isValid, "User not valid");
    session_name("ulogger");
    session_start();
    $_SESSION["user"] = $user;
    unset($user);

    @$auth = new Session();
    $auth->setAuthenticatedIfValid($this->testUser, $this->testPass);
    self::assertTrue($auth->isAuthenticated(), "Should be authenticated");
    self::assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }


  /**
   * @runInSeparateProcess
   */
  public function testIsNotAdmin(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    @$auth = new Session();
    $auth->setAuthenticatedIfValid($this->testUser, $this->testPass);
    self::assertTrue($auth->isAuthenticated(), "Should be authenticated");
    self::assertFalse($auth->isAdmin(), "Should not be admin");
  }

  /**
   * @runInSeparateProcess
   */
  public function testIsAdmin(): void {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT), true);
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    @$auth = new Session();
    $auth->setAuthenticatedIfValid($this->testUser, $this->testPass);
    self::assertTrue($auth->isAuthenticated(), "Should be authenticated");
    self::assertTrue($auth->isAdmin(), "Should be admin");
  }

}

?>
