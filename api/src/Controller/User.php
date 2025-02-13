<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Controller;

use Exception;
use uLogger\Attribute\Route;
use uLogger\Component\Request;
use uLogger\Component\Response;
use uLogger\Component\Session;
use uLogger\Entity;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Mapper;

class User extends AbstractController {

  /**
   * GET /api/users (get all users; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-ADMIN)
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/users', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_ADMIN ]
  ])]
  public function getAll(): Response {
    try {
      $users = $this->mapper(Mapper\User::class)->fetchAll();
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success($users);
  }

  /**
   * GET /users/{id}/tracks (get user tracks; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-OWNER|ADMIN)
   * @param int $userId
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/users/{userId}/tracks', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ]
  ])]
  public function getTracks(int $userId): Response {

    try {
      $tracks = $this->mapper(Mapper\Track::class)->fetchByUser($userId);
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success($tracks);
  }

  /**
   * GET /users/{id}/position (get user last position; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-OWNER|ADMIN)
   * @param int $userId
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/users/{userId}/position', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ]
  ])]
  public function getPosition(int $userId): Response {
    try {
      $position = $this->mapper(Mapper\Position::class)->fetchLast($userId);
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success($position);
  }

  /**
   * GET /users/position (get all users last positions; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-ADMIN)
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/users/position', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_ADMIN ]
  ])]
  public function getAllPosition(): Response {
    $positions = [];
    try {
      $positions = $this->mapper(Mapper\Position::class)->fetchLastAllUsers();
    } catch (NotFoundException) {
      /* ignored */
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success($positions);
  }

  /**
   * PUT /api/users/{id} (for admin to edit other users; access: OPEN-ADMIN, PUBLIC-ADMIN, PRIVATE-ADMIN)
   * @param int $userId
   * @param Entity\User $user
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_PUT, '/api/users/{userId}', [ Session::ACCESS_ALL => [ Session::ALLOW_ADMIN ] ])]
  public function update(int $userId, Entity\User $user): Response {
    $password = $user->password;
    $isAdmin = $user->isAdmin;

    if ($userId !== $user->id) {
      return Response::unprocessableError('Wrong user id');
    }

    try {
      $currentUser = $this->mapper(Mapper\User::class)->fetch($userId);
      if ($userId === $this->session->user->id) {
        return Response::unprocessableError('selfeditwarn');
      }
      $currentUser->isAdmin = $isAdmin;
      $this->mapper(Mapper\User::class)->updateIsAdmin($currentUser);

      if (!empty($password)) {
        if (!$this->config->validPassStrength($password)) {
          return Response::unprocessableError('passstrengthwarn');
        }
        $currentUser->password = $password;
        $this->mapper(Mapper\User::class)->updatePassword($currentUser);

      }
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success();
  }

  /**
   * PUT /api/users/{id}/password (password update; access: OPEN-OWNER, PUBLIC-OWNER, PRIVATE-OWNER)
   * @param int $userId
   * @param string $password
   * @param string $oldPassword
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_PUT, '/api/users/{userId}/password', [ Session::ACCESS_ALL => [ Session::ALLOW_OWNER ] ])]
  public function updatePassword(int $userId, string $password, string $oldPassword): Response {

    if ($this->session->user->id !== $userId) {
      return Response::notAuthorized();
    }

    if (!$this->config->validPassStrength($password)) {
      return Response::unprocessableError('passstrengthwarn');
    }
    if (!$this->session->user->validPassword($oldPassword)) {
      return Response::unprocessableError('oldpassinvalid');
    }

    $this->session->user->password = $password;
    try {
      $this->mapper(Mapper\User::class)->updatePassword($this->session->user);
      $this->session->updateSession();
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success();
  }

  /**
   * POST /api/users (new user; access: OPEN-ADMIN, PUBLIC-ADMIN, PRIVATE-ADMIN)
   * @param Entity\User $user
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_POST, '/api/users', [ Session::ACCESS_ALL => [ Session::ALLOW_ADMIN ] ])]
  public function add(Entity\User $user): Response {

    try {
      if (empty($user->password)) {
        throw new InvalidInputException('Missing password');
      }
      try {
        $this->mapper(Mapper\User::class)->fetchByLogin($user->login);
        return Response::conflictError('userexists');
      } catch (NotFoundException) { /* ignored */ }

      if (!$this->config->validPassStrength($user->password)) {
        return Response::unprocessableError('passstrengthwarn');
      }
      $this->mapper(Mapper\User::class)->create($user);

    } catch (Exception $e) {
      return Response::exception($e);
    }
    // remove password
    $user->password = null;
    return Response::created($user);
  }

  /**
   * DELETE /api/users/{id} (delete user; access: OPEN-ADMIN, PUBLIC-ADMIN, PRIVATE-ADMIN)
   * @param int $userId
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_DELETE, '/api/users/{userId}', [ Session::ACCESS_ALL => [ Session::ALLOW_ADMIN ] ])]
  public function delete(int $userId): Response {

    if ($userId === $this->session->user->id) {
      return Response::unprocessableError('selfeditwarn');
    }

    try {
      $this->mapper(Mapper\Position::class)->deleteAll($userId);
      $this->mapper(Mapper\Track::class)->deleteAll($userId);
      $this->mapper(Mapper\User::class)->delete($userId);
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success();
  }

}
