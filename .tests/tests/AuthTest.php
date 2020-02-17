<?php

require_once(__DIR__ . "/../../helpers/auth.php");
require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/config.php");

class AuthTest extends UloggerDatabaseTestCase {

  public function setUp() {
    $_SESSION = [];
    parent::setUp();
  }

  /**
   * @runInSeparateProcess
   */
  public function testLogin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new uAuth();
    $auth->checkLogin($this->testUser, $this->testPass);
    $this->assertTrue($auth->isAuthenticated(), "Not authenticated");
    $this->assertInstanceOf(uUser::class, $auth->user, "User variable not set");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
    $this->assertEquals($_SESSION["user"]->login, $auth->user->login, "Wrong login");
    $this->assertInstanceOf(uUser::class, $_SESSION["user"], "User not set in session");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginBadPass() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new uAuth();
    $auth->checkLogin($this->testUser, "badPass");
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertInternalType('null', $auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginEmptyLogin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new uAuth();
    $auth->checkLogin("", $this->testPass);
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertInternalType('null', $auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginNoFormData() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new uAuth();
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertInternalType('null', $auth->user, "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testSessionAuth() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new uUser($this->testUser);
    $this->assertTrue($user->isValid, "User not valid");
    session_name("ulogger");
    session_start();
    $_SESSION["user"] = $user;
    unset($user);

    @$auth = new uAuth();
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }

  /**
   * @runInSeparateProcess
   */
  public function testSessionAndRequest() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new uUser($this->testUser);
    $this->assertTrue($user->isValid, "User not valid");
    session_name("ulogger");
    session_start();
    $_SESSION["user"] = $user;
    unset($user);

    @$auth = new uAuth();
    $auth->checkLogin($this->testUser, $this->testPass);
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }


  /**
   * @runInSeparateProcess
   */
  public function testIsNotAdmin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    @$auth = new uAuth();
    $auth->checkLogin($this->testUser, $this->testPass);
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertFalse($auth->isAdmin(), "Should not be admin");
  }

  /**
   * @runInSeparateProcess
   */
  public function testIsAdmin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT), true);
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    @$auth = new uAuth();
    $auth->checkLogin($this->testUser, $this->testPass);
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertTrue($auth->isAdmin(), "Should be admin");
  }

}
?>
