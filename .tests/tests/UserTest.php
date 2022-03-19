<?php

require_once(__DIR__ . "/../lib/UloggerDatabaseTestCase.php");
require_once(__DIR__ . "/../../helpers/user.php");

class UserTest extends UloggerDatabaseTestCase {

  public function testAddUser(): void {
    $userId = uUser::add($this->testUser, $this->testPass);
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    self::assertEquals(1, $userId, "Wrong user id returned");
    $expected = [ "id" => 1, "login" => $this->testUser ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login FROM users");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");

    self::assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
    self::assertFalse(uUser::add($this->testUser, $this->testPass), "Adding user with same login should fail");
    self::assertFalse(uUser::add($this->testUser, ""), "Adding user with empty password should fail");
    self::assertFalse(uUser::add("", $this->testPass), "Adding user with empty login should fail");
  }

  public function testDeleteUser(): void {
    $userId = $this->addTestUser($this->testUser);
    $trackId = $this->addTestTrack($userId);
    $this->addTestPosition($userId, $trackId);

    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(1, $this->getConnection()->getRowCount('positions'), "Wrong row count");

    $user = new uUser($this->testUser);
    $user->delete();
    self::assertEquals(0, $this->getConnection()->getRowCount('users'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('tracks'), "Wrong row count");
    self::assertEquals(0, $this->getConnection()->getRowCount('positions'), "Wrong row count");
    self::assertFalse($user->isValid, "Deleted user should not be valid");
  }

  public function testSetPass(): void {
    $newPass = $this->testPass . "new";
    $this->addTestUser($this->testUser);
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $user = new uUser($this->testUser);
    $user->setPass($newPass);
    self::assertTrue(password_verify($newPass, $this->pdoGetColumn("SELECT password FROM users")), "Wrong actual password hash");
    self::assertFalse($user->setPass(""), "Password should not be empty");

    $userInvalid = new uUser($this->testUser . "-noexistant");
    self::assertFalse($userInvalid->setPass($newPass), "Setting pass for nonexistant user should fail");
  }

  public function testSetAdmin(): void {
    $this->addTestUser($this->testUser);
    self::assertEquals(1, $this->getConnection()->getRowCount('users'), "Wrong row count");
    $user = new uUser($this->testUser);
    self::assertFalse((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should not be admin");
    self::assertFalse($user->isAdmin, "User should not be admin");
    $user->setAdmin(true);
    self::assertTrue((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should be admin");
    self::assertTrue($user->isAdmin, "User should be admin");
    $user->setAdmin(false);
    self::assertFalse((bool) $this->pdoGetColumn("SELECT admin FROM users"), "User should not be admin");
    self::assertFalse($user->isAdmin, "User should not be admin");
  }

  public function testGetAll(): void {
    $this->addTestUser($this->testUser);
    $this->addTestUser($this->testUser2);
    self::assertEquals(2, $this->getConnection()->getRowCount('users'), "Wrong row count");

    $userArr = uUser::getAll();
    self::assertCount(2, $userArr, "Wrong array size");
    self::assertInstanceOf(uUser::class, $userArr[0], "Wrong array member");
  }

  public function testIsAdmin(): void {
    $this->addTestUser($this->testUser, null, true);
    $user = new uUser($this->testUser);
    self::assertTrue($user->isAdmin, "User should be admin");
  }

  public function testIsNotAdmin(): void {
    $this->addTestUser($this->testUser);
    $user = new uUser($this->testUser);
    self::assertFalse($user->isAdmin, "User should not be admin");
  }

  public function testIsValid(): void {
    $this->addTestUser($this->testUser);
    $userValid = new uUser($this->testUser);
    self::assertTrue($userValid->isValid, "User should be valid");
    $userInvalid = new uUser($this->testUser . "-noexistant");
    self::assertFalse($userInvalid->isValid, "User should not be valid");
  }
}
?>
