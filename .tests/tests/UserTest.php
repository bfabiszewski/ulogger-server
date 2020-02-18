<?php

require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/user.php");

class UserTest extends UloggerDatabaseTestCase {

  public function testAddUser() {
    $userId = uUser::add($this->testUser, $this->testPass);
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $this->assertEquals(1, $userId, "Wrong user id returned");
    $expected = [ "id" => 1, "login" => $this->testUser ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login FROM users");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    $this->assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
    $this->assertFalse(uUser::add($this->testUser, $this->testPass), "Adding user with same login should fail");
    $this->assertFalse(uUser::add($this->testUser, ""), "Adding user with empty password should fail");
    $this->assertFalse(uUser::add("", $this->testPass), "Adding user with empty login should fail");
  }

  public function testDeleteUser() {
    $userId = $this->addTestUser($this->testUser);
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);

    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $user = new uUser($this->testUser);
    $user->delete();
    $this->assertEquals(0, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    $this->assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    $this->assertFalse($user->isValid, "Deleted user should not be valid");
  }

  public function testSetPass() {
    $newPass = $this->testPass . "new";
    $this->addTestUser($this->testUser);
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new uUser($this->testUser);
    $user->setPass($newPass);
    $this->assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
    $this->assertFalse($user->setPass(""), "Password should not be empty");

    $userInvalid = new uUser($this->testUser . "-noexistant");
    $this->assertFalse($userInvalid->setPass($newPass), "Setting pass for nonexistant user should fail");
  }

  public function testSetAdmin() {
    $this->addTestUser($this->testUser);
    $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $user = new uUser($this->testUser);
    $this->assertFalse((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should not be admin");
    $this->assertFalse($user->isAdmin, "User should not be admin");
    $user->setAdmin(true);
    $this->assertTrue((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should be admin");
    $this->assertTrue($user->isAdmin, "User should be admin");
    $user->setAdmin(false);
    $this->assertFalse((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should not be admin");
    $this->assertFalse($user->isAdmin, "User should not be admin");
  }

  public function testGetAll() {
    $this->addTestUser($this->testUser);
    $this->addTestUser($this->testUser2);
    $this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $userArr = uUser::getAll();
    $this->assertCount(2, $userArr, "Wrong array size");
    $this->assertInstanceOf(uUser::class, $userArr[0], "Wrong array member");
  }

  public function testIsAdmin() {
    $this->addTestUser($this->testUser, NULL, true);
    $user = new uUser($this->testUser);
    $this->assertTrue($user->isAdmin, "User should be admin");
  }

  public function testIsNotAdmin() {
    $this->addTestUser($this->testUser);
    $user = new uUser($this->testUser);
    $this->assertFalse($user->isAdmin, "User should not be admin");
  }

  public function testIsValid() {
    $this->addTestUser($this->testUser);
    $userValid = new uUser($this->testUser);
    $this->assertTrue($userValid->isValid, "User should be valid");
    $userInvalid = new uUser($this->testUser . "-noexistant");
    $this->assertFalse($userInvalid->isValid, "User should not be valid");
  }
}
?>
