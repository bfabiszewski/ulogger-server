<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../helpers/auth.php");
require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/config.php");

class AuthTest extends UloggerDatabaseTestCase {

  public function setUp() {
    $_REQUEST = [];
    $_SESSION = [];
    parent::setUp();
  }

  private function request($user, $pass) {
    $request = [];
    $request["action"] = "auth";
    $request["user"] = $user;
    $request["pass"] = $pass;
    return $request;
  }

  /**
   * @runInSeparateProcess
   */
  public function testLogin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $_REQUEST = $this->request($this->testUser, $this->testPass);

    $auth = new uAuth();
    $this->assertTrue($auth->isAuthenticated(), "Not authenticated");
    $this->assertTrue($auth->isLoginAttempt(), "Not login attempt");
    $this->assertTrue($auth->user instanceof uUser, "User variable not set");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
    $this->assertEquals($_SESSION["user"]->login, $auth->user->login, "Wrong login");
    $this->assertTrue($_SESSION["user"] instanceof uUser, "User not set in session");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginBadPass() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $_REQUEST = $this->request($this->testUser, "badPass");

    $auth = new uAuth();
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertTrue($auth->isLoginAttempt(), "Not login attempt");
    $this->assertTrue(is_null($auth->user), "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginEmptyLogin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $_REQUEST = $this->request("", $this->testPass);

    $auth = new uAuth();
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertTrue($auth->isLoginAttempt(), "Not login attempt");
    $this->assertTrue(is_null($auth->user), "User not null");
  }

  /**
   * @runInSeparateProcess
   */
  public function testLoginNoFormData() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $auth = new uAuth();
    $this->assertFalse($auth->isAuthenticated(), "Should not be authenticated");
    $this->assertFalse($auth->isLoginAttempt(), "Should not be login attempt");
    $this->assertTrue(is_null($auth->user), "User not null");
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
    $this->assertFalse($auth->isLoginAttempt(), "Should not be login attempt");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }

  /**
   * @runInSeparateProcess
   */
  public function testSessionAndRequest() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $_REQUEST = $this->request($this->testUser, $this->testPass);

    $user = new uUser($this->testUser);
    $this->assertTrue($user->isValid, "User not valid");
    session_name("ulogger");
    session_start();
    $_SESSION["user"] = $user;
    unset($user);

    @$auth = new uAuth();
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertFalse($auth->isLoginAttempt(), "Should not be login attempt");
    $this->assertEquals($this->testUser, $auth->user->login, "Wrong login");
  }


  /**
   * @runInSeparateProcess
   */
  public function testNotIsAdmin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $_REQUEST = $this->request($this->testUser, $this->testPass);

    @$auth = new uAuth();
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertFalse($auth->isAdmin(), "Should not be admin");
  }

  /**
   * @runInSeparateProcess
   */
  public function testIsAdmin() {
    $this->addTestUser($this->testUser, password_hash($this->testPass, PASSWORD_DEFAULT));
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    uConfig::$admin_user = $this->testUser;
    $_REQUEST = $this->request($this->testUser, $this->testPass);

    @$auth = new uAuth();
    $this->assertTrue($auth->isAuthenticated(), "Should be authenticated");
    $this->assertTrue($auth->isAdmin(), "Should not be admin");
  }

}
?>
