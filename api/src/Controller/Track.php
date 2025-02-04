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
use uLogger\Component\FileUpload;
use uLogger\Component\Request;
use uLogger\Component\Response;
use uLogger\Component\Session;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\GpxParseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Gpx;
use uLogger\Helper\Kml;
use uLogger\Mapper;

class Track extends AbstractController {

  /**
   * GET /api/tracks/{id} (get track metadata; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-OWNER:ADMIN)
   * @param int $trackId
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/tracks/{trackId}', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ]
  ])]
  public function get(int $trackId): Response {
    try {
      $track = $this->mapper(Mapper\Track::class)->fetch($trackId);
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success($track);
  }

  /**
   * PUT /api/tracks/{id} (update track metadata; access: OPEN-OWNER:ADMIN, PUBLIC-OWNER:ADMIN, PRIVATE-OWNER:ADMIN)
   * @param int $trackId
   * @param Entity\Track $track
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_PUT, '/api/tracks/{trackId}', [ Session::ACCESS_ALL => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ] ])]
  public function update(int $trackId, Entity\Track $track): Response {
    if ($trackId !== $track->id) {
      return Response::unprocessableError('Wrong track id');
    }
    try {
      $this->mapper(Mapper\Track::class)->update($track);
    } catch (Exception $e) {
      return Response::exception($e);
    }
    return Response::success();
  }

  /**
   * POST /api/tracks (add track; access: OPEN-OWNER:ADMIN, PUBLIC-OWNER:ADMIN, PRIVATE-OWNER:ADMIN)
   * @param Entity\Track $track
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_POST, '/api/tracks', [ Session::ACCESS_ALL => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ] ])]
  #[Route(Request::METHOD_POST, '/api/client/tracks', [ Session::ACCESS_ALL => [ Session::ALLOW_OWNER ] ])]
  public function add(Entity\Track $track): Response {

    try {
      $this->mapper(Mapper\Track::class)->create($track);
    } catch (Exception $e) {
      return Response::exception($e);
    }
    return Response::created($track);
  }

  /**
   * DELETE /api/tracks/{id} (delete track; access: OPEN-OWNER|ADMIN, PUBLIC-OWNER|ADMIN, PRIVATE-OWNER|ADMIN)
   * @param int $trackId
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_DELETE, '/api/tracks/{trackId}', [ Session::ACCESS_ALL => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ] ])]
  public function delete(int $trackId): Response {

    try {
      $track = $this->mapper(Mapper\Track::class)->fetch($trackId);
      $this->mapper(Mapper\Position::class)->deleteAll($track->userId, $track->id);
      $this->mapper(Mapper\Track::class)->delete($track);
    } catch (Exception $e) {
      return Response::exception($e);
    }

    return Response::success();
  }

  /**
   * POST /api/tracks/import (import uploaded file; access: OPEN-AUTHORIZED, PUBLIC-AUTHORIZED, PRIVATE-AUTHORIZED)
   * @param FileUpload $gpxUpload
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_POST, '/api/tracks/import', [ Session::ACCESS_ALL => [ Session::ALLOW_AUTHORIZED ] ])]
  public function import(FileUpload $gpxUpload): Response {
    $gpxFile = $gpxUpload->getTmpName();
    $gpxName = basename($gpxUpload->getName());
    try {
      $tracks = $this->getImportedTracks($gpxName, $gpxFile);
      return Response::created($tracks);
    } catch (Exception $e) {
      return Response::exception($e);
    } finally {
      if (file_exists($gpxFile)) {
        unlink($gpxFile);
      }
    }
  }

  /**
   * GET /api/tracks/{trackId}/export?format={format} (download exported file; access: OPEN-ALL, PUBLIC-AUTHORIZED, PRIVATE-OWNER|ADMIN)
   * @param int $trackId
   * @param string $format
   * @return Response
   * @noinspection PhpUnused
   */
  #[Route(Request::METHOD_GET, '/api/tracks/{trackId}/export', [
    Session::ACCESS_OPEN => [ Session::ALLOW_ALL ],
    Session::ACCESS_PUBLIC => [ Session::ALLOW_AUTHORIZED ],
    Session::ACCESS_PRIVATE => [ Session::ALLOW_OWNER, Session::ALLOW_ADMIN ],
    ]
  )]
  public function export(int $trackId, string $format): Response {

    try {
      $track = $this->mapper(Mapper\Track::class)->fetch($trackId);
      $positions = $this->mapper(Mapper\Position::class)->findAll($trackId);

      if (empty($positions)) {
        return Response::notFound();
      }

      $file = $this->getFile($format, $track);
    } catch (Exception $e) {
      return Response::exception($e);
    }
    return Response::fileAttachment($file->export($positions));
  }

  /**
   * @param string $gpxName
   * @param string $gpxFile
   * @return Entity\Track[]
   * @throws DatabaseException
   * @throws GpxParseException
   * @throws ServerException
   */
  protected function getImportedTracks(string $gpxName, string $gpxFile): array {
    $gpx = new Gpx($gpxName, $this->config, $this->mapperFactory);
    return $gpx->import($this->session->user->id, $gpxFile);
  }

  /**
   * @param string $format
   * @param Entity\Track $track
   * @return Gpx|Kml
   * @throws InvalidInputException
   * @throws ServerException
   */
  protected function getFile(string $format, Entity\Track $track): Kml|Gpx {
    return match ($format) {
      'gpx' => new Gpx($track->name, $this->config, $this->mapperFactory),
      'kml' => new Kml($track->name, $this->config),
      default => throw new InvalidInputException("Unsupported format: $format"),
    };
  }

}
