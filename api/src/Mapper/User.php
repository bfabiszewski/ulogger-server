<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Mapper;

use PDOException;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;

class User extends AbstractMapper {


  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   */
  public function fetch(int $userId): Entity\User {
    $users = $this->get(userId: $userId);
    if (empty($users)) {
      throw new NotFoundException();
    }
    return $users[0];
  }

  /**
   * @param string $login
   * @return Entity\User
   * @throws DatabaseException
   * @throws ServerException
   * @throws NotFoundException
   */
  public function fetchByLogin(string $login): Entity\User {
    $users = $this->get(login: $login);
    if (empty($users)) {
      throw new NotFoundException();
    }
    return $users[0];
  }

  /**
   * @return Entity\User[]
   * @throws DatabaseException
   * @throws ServerException
   */
  public function fetchAll(): array {
    return $this->get();
  }

  /**
   * Add new user
   *
   * @param Entity\User $user
   * @throws InvalidInputException
   * @throws DatabaseException
   */
  public function create(Entity\User $user): void {
    if (empty($user->password)) {
      throw new InvalidInputException('User password is required');
    }
    $user->hash = password_hash($user->password, PASSWORD_DEFAULT);
    $table = $this->db->table('users');
    try {
      $query = "INSERT INTO $table (login, password, admin) VALUES (?, ?, ?)";
      $stmt = $this->db->prepare($query);
      $stmt->execute([ $user->login, $user->hash, (int) $user->isAdmin ]);
      $user->id = (int) $this->db->lastInsertId("{$table}_id_seq");
    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }

  }

  /**
   * Set user admin status
   *
   * @param Entity\User $user
   * @throws DatabaseException
   */
  public function updateIsAdmin(Entity\User $user): void {
    try {
      $query = 'UPDATE ' . $this->db->table('users') . ' SET admin = ? WHERE id = ?';
      $stmt = $this->db->prepare($query);
      $stmt->execute([ (int) $user->isAdmin, $user->id ]);
    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }
  }

  /**
   * Set user password
   *
   * @param Entity\User $user
   * @throws DatabaseException
   * @throws InvalidInputException
   */
  public function updatePassword(Entity\User $user): void {
    if (empty($user->password)) {
      throw new InvalidInputException('Missing password');
    }

    $hash = password_hash($user->password, PASSWORD_DEFAULT);
    try {
      $query = 'UPDATE ' . $this->db->table('users') . ' SET password = ? WHERE id = ?';
      $stmt = $this->db->prepare($query);
      $stmt->execute([ $hash, $user->id ]);

    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }
  }

  /**
   * Delete user
   * This will also delete all user's positions and tracks
   *
   * @throws DatabaseException
   */
  public function delete(int $userId): void {

    try {
      $query = 'DELETE FROM ' . $this->db->table('users') . ' WHERE id = ?';
      $stmt = $this->db->prepare($query);
      $stmt->execute([ $userId ]);

    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }

  }

  /**
   * @param int|null $userId
   * @param string|null $login
   * @return Entity\User[]
   * @throws DatabaseException
   * @throws ServerException
   */
  private function get(?int $userId = null, ?string $login = null): array {

    try {
      $query = 'SELECT id, login, password, admin FROM ' . $this->db->table('users');
      $params = null;
      if ($userId) {
        $query .= ' WHERE id = ? LIMIT 1';
        $params[] = $userId;
      } elseif ($login) {
        $query .= ' WHERE login = ? LIMIT 1';
        $params[] = $login;
      }
      $stmt = $this->db->prepare($query);
      $stmt->execute($params);

      $users = [];
      while ($row = $stmt->fetch()) {
        $users[] = Entity\User::fromDatabaseRow($row);
      }
      return $users;
    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }
  }


  /**
   * Store user id in session
   * @param Entity\User $user
   * @throws InvalidInputException
   */
  public function storeInSession(Entity\User $user): void {
    if (!$user->id) {
      throw new InvalidInputException('User not valid');
    }
    $_SESSION['user_id'] = $user->id;

  }

  /**
   * Retrieve user id from session data
   * @return int User Id
   * @throws NotFoundException
   */
  public function getFromSession(): int {
    if (isset($_SESSION['user_id'])) {
      return $_SESSION['user_id'];
    }
    throw new NotFoundException();
  }

}
