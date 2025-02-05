<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\TestCase;
use uLogger\Entity\User;

class UserTest extends TestCase {

  public function testConstructorSetsLogin(): void {
    $login = 'test_user';
    $user = new User($login);
    $this->assertSame($login, $user->login);
  }

  public function testDefaultValues(): void {
    $user = new User('test_user');

    $this->assertNull($user->id);
    $this->assertNull($user->hash);
    $this->assertNull($user->password);
    $this->assertFalse($user->isAdmin);
  }

  public function testValidPasswordReturnsTrueForMatchingHash(): void {
    $user = new User('test_user');
    $password = 'test_pass';
    $user->hash = password_hash($password, PASSWORD_DEFAULT);

    $this->assertTrue($user->validPassword($password));
  }

  public function testValidPasswordReturnsFalseForNonMatchingHash(): void {
    $user = new User('test_user');
    $user->hash = password_hash('correct_pass', PASSWORD_DEFAULT);

    $this->assertFalse($user->validPassword('wrong_pass'));
  }

  public function testValidPasswordReturnsFalseWhenHashIsNull(): void {
    $user = new User('test_user');

    $this->assertFalse($user->validPassword('any_pass'));
  }
}

?>
