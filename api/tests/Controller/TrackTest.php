<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Controller;

use PHPUnit\Framework\MockObject\Exception as MockException;
use uLogger\Component\FileUpload;
use uLogger\Component\Response;
use uLogger\Controller;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Gpx;
use uLogger\Mapper;

class TrackTest extends AbstractControllerTestCase
{
  private Controller\Track $controller;

  protected function setUp(): void {
    parent::setUp();

    $this->controller = new Controller\Track($this->mapperFactory, $this->session, $this->config);
  }

  // get

  /**
   * @throws MockException|ServerException
   */
  public function testGetTrackSuccess() {
    $trackId = 123;

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackId)
      ->willReturn($trackMock);

    $response = $this->controller->get($trackId);

    $this->assertResponseSuccessWithPayload($response, $trackMock);
  }

  /**
   * @throws ServerException
   */
  public function testGetTrackException() {
    $trackId = 123;
    $exception = new DatabaseException();

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackId)
      ->willThrowException($exception);

    $response = $this->controller->get($trackId);

    $this->assertResponseException($response, $exception);
  }

  // update

  /**
   * @throws MockException|ServerException
   */
  public function testUpdateTrackSuccess() {
    $trackId = 123;

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('update')
      ->with($trackMock);

    $response = $this->controller->update($trackId, $trackMock);

    $this->assertResponseSuccessNoPayload($response);
  }

  /**
   * @throws MockException
   */
  public function testUpdateTrackIdMismatch() {
    $trackId = 123;

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = 456; // Mismatched track ID

    $response = $this->controller->update($trackId, $trackMock);

    $this->assertResponseUnprocessableError($response, 'Wrong track id');
  }

  /**
   * @throws MockException|ServerException
   */
  public function testUpdateTrackException() {
    $trackId = 123;
    $exception = new DatabaseException();

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('update')
      ->with($trackMock)
      ->willThrowException($exception);

    $response = $this->controller->update($trackId, $trackMock);

    $this->assertResponseException($response, $exception);
  }

  // add

  /**
   * @throws MockException|ServerException
   */
  public function testAddTrackSuccess() {
    $trackId = 123;

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;
    $trackMock->name = 'Test track';

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('create')
      ->with($trackMock);

    $response = $this->controller->add($trackMock);

    $this->assertResponseCreatedWithPayload($response, $trackMock);
  }

  /**
   * @throws MockException|ServerException
   */
  public function testAddTrackEmptyName() {
    $trackId = 123;

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;
    $trackMock->name = '';

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->never())
      ->method('create')
      ->with($trackMock);

    $response = $this->controller->add($trackMock);

    $this->assertResponseUnprocessableError($response, 'Track name cannot be empty');
  }

  /**
   * @throws MockException|ServerException
   */
  public function testAddTrackException() {
    $trackId = 123;
    $exception = new DatabaseException();

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;
    $trackMock->name = 'Test track';

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('create')
      ->with($trackMock)
      ->willThrowException($exception);

    $response = $this->controller->add($trackMock);

    $this->assertResponseException($response, $exception);
  }

  // delete

  /**
   * @throws MockException|ServerException
   */
  public function testDeleteTrackSuccess() {
    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = 123;
    $trackMock->userId = 456;

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackMock->id)
      ->willReturn($trackMock);
    $this->mapperMock(Mapper\Position::class)
      ->expects($this->once())
      ->method('deleteAll')
      ->with($trackMock->userId, $trackMock->id);
    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('delete')
      ->with($trackMock);

    $response = $this->controller->delete($trackMock->id);

    $this->assertResponseSuccessNoPayload($response);
  }

  /**
   * @throws MockException|ServerException
   */
  public function testDeleteTrackException() {
    $trackId = 123;
    $exception = new DatabaseException();

    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = $trackId;

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackMock->id)
      ->willThrowException($exception);

    $response = $this->controller->delete($trackMock->id);

    $this->assertResponseException($response, $exception);
  }

  // import

  /**
   * @throws MockException
   */
  public function testImportSuccess() {
    $tmpFile = '/tmp/file.gpx';
    $gpxName = 'file.gpx';
    $userId = 123;
    $tracks = [
      $this->createMock(Entity\Track::class)
    ];

    // Mock the FileUpload object.
    $gpxUploadMock = $this->createMock(FileUpload::class);
    $gpxUploadMock->expects($this->once())
      ->method('getTmpName')
      ->willReturn($tmpFile);
    $gpxUploadMock->expects($this->once())
      ->method('getName')
      ->willReturn($gpxName);

    // Mock the session user.
    $this->session->user = new Entity\User('admin');
    $this->session->user->id = $userId;

    $this->controller = $this->getMockBuilder(Controller\Track::class)
      ->setConstructorArgs([$this->mapperFactory, $this->session, $this->config])
      ->onlyMethods([ 'getImportedTracks' ])
      ->getMock();
    $this->controller->method('getImportedTracks')
      ->with($gpxName, $tmpFile)
      ->willReturn($tracks);

    $response = $this->controller->import($gpxUploadMock);

    $this->assertResponseCreatedWithPayload($response, $tracks);
  }

  /**
   * @throws MockException
   */
  public function testImportException() {
    $tmpFile = '/tmp/file.gpx';
    $gpxName = 'file.gpx';
    $userId = 123;

    $exception = new DatabaseException();

    // Mock the FileUpload object.
    $gpxUploadMock = $this->createMock(FileUpload::class);
    $gpxUploadMock->expects($this->once())
      ->method('getTmpName')
      ->willReturn($tmpFile);
    $gpxUploadMock->expects($this->once())
      ->method('getName')
      ->willReturn($gpxName);

    // Mock the session user.
    $this->session->user = new Entity\User('admin');
    $this->session->user->id = $userId;

    $this->controller = $this->getMockBuilder(Controller\Track::class)
      ->setConstructorArgs([$this->mapperFactory, $this->session, $this->config])
      ->onlyMethods([ 'getImportedTracks' ])
      ->getMock();
    $this->controller->method('getImportedTracks')
      ->with($gpxName, $tmpFile)
      ->willThrowException($exception);

    $response = $this->controller->import($gpxUploadMock);

    $this->assertResponseException($response, $exception);
  }

  // export

  /**
   * @throws MockException|ServerException
   */
  public function testExportSuccess() {
    $format = 'gpx';
    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = 123;
    $positionsMock = [
      $this->createMock(Entity\Position::class),
      $this->createMock(Entity\Position::class)
    ];

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackMock->id)
      ->willReturn($trackMock);
    $this->mapperMock(Mapper\Position::class)
      ->expects($this->once())
      ->method('findAll')
      ->with($trackMock->id)
      ->willReturn($positionsMock);

    $gpxMock = $this->createMock(Gpx::class);
    $fileMock = $this->createMock(Entity\File::class);

    $this->controller = $this->getMockBuilder(Controller\Track::class)
      ->setConstructorArgs([$this->mapperFactory, $this->session, $this->config])
      ->onlyMethods([ 'getFile' ])
      ->getMock();
    $this->controller->method('getFile')
      ->with($format, $trackMock)
      ->willReturn($gpxMock);
    $gpxMock
      ->expects($this->once())
      ->method('export')
      ->with($positionsMock)
      ->willReturn($fileMock);

    $response = $this->controller->export($trackMock->id, $format);

    $this->assertEquals(Response::fileAttachment($fileMock), $response);
  }

  /**
   * @throws MockException|ServerException
   */
  public function testExportNotFoundException() {
    $format = 'gpx';
    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = 123;
    // empty positions array
    $positionsMock = [];

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackMock->id)
      ->willReturn($trackMock);
    $this->mapperMock(Mapper\Position::class)
      ->expects($this->once())
      ->method('findAll')
      ->with($trackMock->id)
      ->willReturn($positionsMock);

    $gpxMock = $this->createMock(Gpx::class);

    $this->controller = $this->getMockBuilder(Controller\Track::class)
      ->setConstructorArgs([$this->mapperFactory, $this->session, $this->config])
      ->onlyMethods([ 'getFile' ])
      ->getMock();
    $this->controller
      ->expects($this->never())
      ->method('getFile');
    $gpxMock
      ->expects($this->never())
      ->method('export');

    $response = $this->controller->export($trackMock->id, $format);

    $this->assertEquals(Response::notFound(), $response);
  }

  /**
   * @throws MockException|ServerException
   */
  public function testExportOtherException() {
    $format = 'gpx';
    $trackMock = $this->createMock(Entity\Track::class);
    $trackMock->id = 123;

    $exception = new DatabaseException();

    $this->mapperMock(Mapper\Track::class)
      ->expects($this->once())
      ->method('fetch')
      ->with($trackMock->id)
      ->willThrowException($exception);
    $this->mapperMock(Mapper\Position::class)
      ->expects($this->never())
      ->method('findAll');

    $gpxMock = $this->createMock(Gpx::class);

    $this->controller = $this->getMockBuilder(Controller\Track::class)
      ->setConstructorArgs([$this->mapperFactory, $this->session, $this->config])
      ->onlyMethods([ 'getFile' ])
      ->getMock();
    $this->controller
      ->expects($this->never())
      ->method('getFile');
    $gpxMock
      ->expects($this->never())
      ->method('export');

    $response = $this->controller->export($trackMock->id, $format);

    $this->assertResponseException($response, $exception);
  }

}
