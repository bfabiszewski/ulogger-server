<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Component;

use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;
use uLogger\Mapper\MapperFactory;
use uLogger\Mapper;

/**
 * Authentication
 */
class Session {

  public const ACCESS_OPEN = 'access open';
  public const ACCESS_PUBLIC = 'access public';
  public const ACCESS_PRIVATE = 'access private';
  public const ACCESS_ALL = 'access all';
  public const ALLOW_ALL = 'allow all';
  public const ALLOW_AUTHORIZED = 'allow authorized';
  public const ALLOW_OWNER = 'allow owner';
  public const ALLOW_ADMIN = 'allow admin';

  /** @var Mapper\User */
  private Mapper\User $userMapper;
  private Entity\Config $config;
  /** @var bool Is user authenticated */
  private bool $isAuthenticated = false;
  /** @var null|Entity\User */
  public ?Entity\User $user = null;

  /**
   * @throws ServerException
   */
  public function __construct(MapperFactory $mapperFactory, Entity\Config $config) {
    $this->userMapper = $mapperFactory->getMapper(Mapper\User::class);
    $this->config = $config;
  }

  /**
   * @return void
   * @throws DatabaseException
   * @throws ServerException
   */
  public function init(): void {

    $this->sessionStart();

    try {
      $userId = $this->userMapper->getFromSession();
      $user = $this->userMapper->fetch($userId);
      $this->setAuthenticated($user);
    } catch (NotFoundException) { /* ignored */ }
  }

  /**
   * config
   * - R/A (require authorization)
   * - P/A (implies R/A, public access)
   *
   * access types
   * - OPEN (!R/A)
   * - PUBLIC (R/A && P/A)
   * - PRIVATE (R/A && !P/A)
   * @return string
   */
  public function getAccessType(): string {
    if (!$this->config->requireAuthentication) {
      return self::ACCESS_OPEN;
    } elseif ($this->config->requireAuthentication && $this->config->publicTracks) {
      return self::ACCESS_PUBLIC;
    }
    return self::ACCESS_PRIVATE;
  }

  /**
   * Update user instance stored in session
   * @throws InvalidInputException
   */
  public function updateSession(): void {
    if ($this->isAuthenticated()) {
      $this->userMapper->storeInSession($this->user);
    }
  }

  /**
   * Is user authenticated
   *
   * @return bool True if authenticated, false otherwise
   */
  public function isAuthenticated(): bool {
    return $this->isAuthenticated;
  }

  /**
   * Is authenticated user admin
   *
   * @return bool True if admin, false otherwise
   */
  public function isAdmin(): bool {
    return $this->isAuthenticated && $this->user->isAdmin;
  }

  public function isSessionUser(int $userId): bool {
    return $this->isAuthenticated && $this->user->id === $userId;
  }

  /**
   * Start php session
   *
   * @return void
   */
  private function sessionStart(): void {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
      (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    // TODO: add config param for session lifetime
//    ini_set('session.gc_maxlifetime', (string) $this->config->sessionLifetime);
//    ini_set('session.gc_probability', '1');
//    ini_set('session.gc_divisor', '25');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
      'lifetime' => 0,
      'httponly' => true,
      'samesite' => 'Lax',
      'secure' => $isHttps
    ]);
    session_name('ulogger');
    session_start();
  }

  /**
   * Terminate php session
   *
   * @return void
   */
  public function sessionEnd(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies') && isset($_COOKIE[session_name()])) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
    session_destroy();
  }

  /**
   * Clean session variables
   *
   * @return void
   */
  private function sessionCleanup(): void {
    $_SESSION = [];
  }

  /**
   * Mark as authenticated, set user
   *
   * @param Entity\User $user
   * @return void
   */
  private function setAuthenticated(Entity\User $user): void {
    $this->isAuthenticated = true;
    $this->user = $user;
  }

  /**
   * @param Entity\User $user
   * @return void
   * @throws InvalidInputException
   */
  public function setAuthenticatedAndStore(Entity\User $user): void {
    $this->setAuthenticated($user);
    $this->sessionCleanup();
    $this->userMapper->storeInSession($user);
  }

  /**
   * Check valid pass for given login
   *
   * @param string $login
   * @param string $password
   * @throws DatabaseException
   * @throws ServerException
   * @throws InvalidInputException
   * @throws NotFoundException On wrong login or password
   */
  public function setAuthenticatedIfValid(string $login, string $password): void {
    $user = $this->userMapper->fetchByLogin($login);
    if (!$user->validPassword($password)) {
      throw new NotFoundException();
    }
    $this->setAuthenticatedAndStore($user);
  }

  /**
   * Log out
   *
   * @return void
   */
  public function logOut(): void {
    $this->sessionEnd();
  }

}
