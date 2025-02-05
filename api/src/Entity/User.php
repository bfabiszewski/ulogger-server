<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Entity;

use uLogger\Attribute\Column;
use uLogger\Attribute\JsonField;

class User extends AbstractEntity {
  #[Column]
  #[JsonField]
  public ?int $id = null;
  #[Column]
  #[JsonField]
  public string $login;
  #[Column(name: 'password')]
  public ?string $hash = null;
  public ?string $password = null;
  #[Column(name: 'admin')]
  #[JsonField]
  public bool $isAdmin = false;

  /**
   * @param string $login
   */
  public function __construct(string $login) {
    $this->login = $login;
  }


  /**
   * Check if given password matches user's one
   *
   * @param string $password Password
   * @return bool True if matches, false otherwise
   */
  public function validPassword(string $password): bool {
    if (empty($this->hash)) {
      return false;
    }
    return password_verify($password, $this->hash);
  }

}
?>
