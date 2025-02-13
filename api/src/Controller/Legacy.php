<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Controller;

use ReflectionException;
use uLogger\Attribute\Route;
use uLogger\Component;
use uLogger\Component\Request;
use uLogger\Component\Response;
use uLogger\Component\Session;
use uLogger\Entity;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;
use uLogger\Exception\UnauthorizedException;
use uLogger\Mapper\MapperFactory;

class Legacy extends AbstractController {
  private Component\Router $router;

  public function __construct(MapperFactory $mapperFactory, Session $session, Entity\Config $config, Component\Router $router) {
    $this->router = $router;
    parent::__construct($mapperFactory, $session, $config);
  }

  /**
   * @param string $action
   * @param mixed ...$params
   * @return Response
   */
  #[Route(Request::METHOD_POST, '/client/index.php', [ Component\Session::ACCESS_ALL => [ Component\Session::ALLOW_ALL ] ])]
  public function client(string $action, mixed ...$params): Response {
    try {
      $response = match ($action) {
        'auth' => $this->session($params),
        'addtrack' => $this->track($params),
        'addpos' => $this->position($params),
        default => throw new NotFoundException(),
      };
    } catch (UnauthorizedException) {
      $response = Response::notAuthorized();
    } catch (NotFoundException|ServerException|ReflectionException|InvalidInputException $e) {
      $message = empty($e->getMessage()) ? 'Error ' . get_class($e) : $e->getMessage();
      $response = Response::success([ 'error' => true, 'message' => $message ]);
    }
    return $response;
  }

  /**
   * @param array $params
   * @return Response
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   */
  private function session(array $params): Response {
    $request = new Request(
      path: '/api/client/session',
      method: Request::METHOD_POST,
      payload: [
        'login' => $params['user'] ?? null,
        'password' => $params['pass'] ?? null,
      ]
    );
    return $this->rewriteResponse($this->router->dispatch($request));
  }

  /**
   * @param array $params
   * @return Response
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   * @throws UnauthorizedException
   */
  private function track(array $params): Response {
    $request = new Request(
      path: '/api/client/tracks',
      method: Request::METHOD_POST,
      payload: [
        'name' => $params['track'] ?? null,
        'userId' => $this->session->user->id ?? throw new UnauthorizedException(),
      ]
    );
    $response = $this->router->dispatch($request);

    if ($response->getCode() === Response::CODE_2_CREATED) {
      $payload = $response->getPayload();
      $trackId = $payload->id ?? null;
      return Response::success([ 'error' => false, 'trackid' => $trackId ]);
    }
    return $this->rewriteResponse($response);
  }

  /**
   * @param array $params
   * @return Response
   * @throws InvalidInputException
   * @throws ReflectionException
   * @throws ServerException
   * @throws UnauthorizedException
   */
  private function position(array $params): Response {
    $request = new Request(
      path: '/api/client/positions',
      method: Request::METHOD_POST,
      payload: [
        'latitude' => $params['lat'] ?? null,
        'longitude' => $params['lon'] ?? null,
        'timestamp' => $params['time'] ?? null,
        'altitude' => $params['altitude'] ?? null,
        'speed' => $params['speed'] ?? null,
        'bearing' => $params['bearing'] ?? null,
        'accuracy' => $params['accuracy'] ?? null,
        'provider' => $params['provider'] ?? null,
        'comment' => $params['comment'] ?? null,
        'trackId' => $params['trackid'] ?? null,
        'userId' => $this->session->user->id ?? throw new UnauthorizedException()
      ],
      uploads: !empty($params['image']) ? [ 'image' => $params['image'] ] : []
    );
    return $this->rewriteResponse($this->router->dispatch($request));
  }

  private function rewriteResponse(Response $response): Response {
    if ((int) ($response->getCode() / 100) === 2) {
      return Response::success([ 'error' => false ]);
    } elseif ($response->getCode() === Response::CODE_4_UNAUTHORIZED) {
      return $response;
    } else {
      $message = $response->getPayload()['message'] ?? "Error {$response->getCode()}";
      return Response::success([ 'error' => true, 'message' => $message ]);
    }
  }

}
