<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper;

use DateTimeImmutable;
use DateTimeZone;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use uLogger\Component\Db;
use uLogger\Component\FileUpload;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;
use uLogger\Mapper;
use uLogger\Mapper\MapperFactory;

class PositionTest extends AbstractMapperTestCase {
  protected Mapper\Position $mapper;

  /**
   * @throws ServerException
   * @throws DatabaseException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   * @throws \Exception
   */
  public function testFetchSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $positionId = 1;
    $position = $this->mapper->fetch($positionId);

    $record = $this->getRecordById(Fixtures\Positions::class, $positionId);
    $this->assertPositionEquals($record, $position);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   */
  public function testFetchNotFound() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $positionId = 111; // non existing

    $this->expectException(NotFoundException::class);

    $this->mapper->fetch($positionId);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   * @throws Exception
   */
  public function testFetchDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $positionId = 1;

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('query')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->fetch($positionId);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   * @throws \Exception
   */
  public function testFetchLastSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);
    $expected = $this->getRecordById(Fixtures\Positions::class, 4);

    $userId = 1;
    $position = $this->mapper->fetchLast($userId);

    $this->assertPositionEquals($expected, $position);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   */
  public function testFetchLastNotFound() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $userId = 111; // non existing

    $this->expectException(NotFoundException::class);

    $this->mapper->fetchLast($userId);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   * @throws \Exception
   */
  public function testFetchLastAllUsersSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $record1 = $this->getRecordById(Fixtures\Positions::class, 3);
    $record2 = $this->getRecordById(Fixtures\Positions::class, 4);

    $positions = $this->mapper->fetchLastAllUsers();

    $this->assertCount(2, $positions);
    $this->assertPositionEquals($record1, $positions[0]);
    $this->assertPositionEquals($record2, $positions[1]);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws NotFoundException
   */
  public function testFetchLastAllUsersNotFound() {

    $this->expectException(NotFoundException::class);

    $this->mapper->fetchLastAllUsers();
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws \Exception
   */
  public function testFindAllSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $record1 = $this->getRecordById(Fixtures\Positions::class, 1);
    $record2 = $this->getRecordById(Fixtures\Positions::class, 2);

    $trackId = 1;
    $positions = $this->mapper->findAll($trackId);

    $this->assertCount(2, $positions);
    $this->assertPositionEquals($record1, $positions[0]);
    $this->assertPositionEquals($record2, $positions[1]);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws \Exception
   */
  public function testFindAllAfterIdSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);
    $record1 = $this->getRecordById(Fixtures\Positions::class, 2);

    $trackId = 1;
    $afterId = 1;
    $positions = $this->mapper->findAll($trackId, $afterId);

    $this->assertCount(1, $positions);
    $this->assertPositionEquals($record1, $positions[0]);
  }

  /**
   * @throws DatabaseException
   * @throws \Exception
   */
  public function testCreateSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);
    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->altitude = 0.1;
    $position->speed = 0.2;
    $position->bearing = 0.3;
    $position->accuracy = 4;
    $position->provider = 'test_provider';
    $position->comment = 'test_comment';
    $position->image = 'test_image.jpg';
    $this->assertTableRowCount(4, 'positions');

    $this->mapper->create($position);

    $this->assertTableRowCount(5, 'positions');
    $this->assertPositionEquals($this->getTableRowById('positions', 5), $position);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws Exception
   */
  public function testCreateDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = $this->createMock(Entity\Position::class);

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->create($position);
  }

  /**
   * @throws DatabaseException
   * @throws \Exception
   */
  public function testUpdateSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->altitude = 0.1;
    $position->speed = 0.2;
    $position->bearing = 0.3;
    $position->accuracy = 4;
    $position->provider = 'test_provider';
    $position->comment = 'test_comment';
    $position->image = 'test_image.jpg';
    $this->assertTableRowCount(4, 'positions');

    $this->mapper->update($position);

    $this->assertTableRowCount(4, 'positions');
    $this->assertPositionEquals($this->getTableRowById('positions', 1), $position);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   * @throws Exception
   */
  public function testUpdateDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = $this->createMock(Entity\Position::class);

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->update($position);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   */
  public function testDeleteSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;

    $this->assertTableRowCount(4, 'positions');

    $this->mapper->delete($position);

    $this->assertTableRowCount(3, 'positions');
    $this->assertTableRowNotExists('positions', $position->id);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   */
  public function testDeleteWithImageSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'test_image.jpg';

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image);

    $this->assertTableRowCount(4, 'positions');

    $this->mapper->delete($position);

    $this->assertTableRowCount(3, 'positions');
    $this->assertTableRowNotExists('positions', $position->id);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   */
  public function testDeleteWithImageNotFoundSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'test_image.jpg';

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image)
      ->willThrowException(new NotFoundException());

    $this->assertTableRowCount(4, 'positions');

    $this->mapper->delete($position);

    $this->assertTableRowCount(3, 'positions');
    $this->assertTableRowNotExists('positions', $position->id);
  }

  /**
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = $this->createMock(Entity\Position::class);
    $position->id = 1;

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->delete($position);
  }

  /**
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteSystemFileNotFound() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = $this->createMock(Entity\Position::class);
    $position->id = 1;

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->delete($position);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws InvalidInputException
   * @throws Exception
   */
  public function testSetImageSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'old_image.jpg';
    $newImage = 'new_image.jpg';

    $fileUpload = $this->createMock(FileUpload::class);
    $fileUpload->expects($this->once())
      ->method('add')
      ->with($position->trackId)
      ->willReturn($newImage);

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image);

    $this->mapper->setImage($position, $fileUpload);

    $this->assertEquals($newImage, $position->image);
    $this->assertTableRowValue($position->image, 'positions', $position->id, 'image');
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws InvalidInputException
   * @throws Exception
   */
  public function testSetImageNotFoundSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'old_image.jpg';
    $newImage = 'new_image.jpg';

    $fileUpload = $this->createMock(FileUpload::class);
    $fileUpload->expects($this->once())
      ->method('add')
      ->with($position->trackId)
      ->willReturn($newImage);

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image)
      ->willThrowException(new NotFoundException());

    $this->mapper->setImage($position, $fileUpload);

    $this->assertEquals($newImage, $position->image);
    $this->assertTableRowValue($position->image, 'positions', $position->id, 'image');
  }

  /**
   * @throws ServerException
   * @throws Exception
   * @throws InvalidInputException
   */
  public function testSetImageDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = $this->createMock(Entity\Position::class);
    $position->id = 1;
    $position->trackId = 1;

    $newImage = 'new_image.jpg';

    $fileUpload = $this->createMock(FileUpload::class);
    $fileUpload->expects($this->once())
      ->method('add')
      ->with($position->trackId)
      ->willReturn($newImage);

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->setImage($position, $fileUpload);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testRemoveImageSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'test_image.jpg';

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image);

    $this->mapper->removeImage($position);

    $this->assertNull($position->image);
    $this->assertTableRowValue($position->image, 'positions', $position->id, 'image');
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testRemoveImageNoImageSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = null;

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->never())
      ->method('removeFromFilesystem')
      ->with($position->image);

    $this->mapper->removeImage($position);

    $this->assertNull($position->image);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testRemoveImageNotFoundSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'test_image.jpg';

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->with($position->image)
      ->willThrowException(new NotFoundException());

    $this->mapper->removeImage($position);

    $this->assertNull($position->image);
    $this->assertTableRowValue($position->image, 'positions', $position->id, 'image');
  }

  /**
   * @throws ServerException
   * @throws Exception
   */
  public function testRemoveImageDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $position = new Entity\Position(1727964315, 1, 1, 55.0, 22.0);
    $position->id = 1;
    $position->image = 'new_image.jpg';

    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->removeImage($position);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteAllSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $userId = 1;

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();

    $this->assertTableRowCount(4, 'positions');

    $this->mapper->deleteAll($userId);

    $this->assertTableRowCount(1, 'positions');
    $this->assertTableRowExists('positions', 3);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteAllByTrackSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $userId = 1;
    $trackId = 1;

    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();

    $this->assertTableRowCount(4, 'positions');

    $this->mapper->deleteAll($userId, $trackId);

    $this->assertTableRowCount(2, 'positions');
    $this->assertTableRowNotExists('positions', 1);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteAllDatabaseException() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $userId = 1;

    $stmt = $this->createMock(PDOStatement::class);
    $stmt->expects($this->once())
      ->method('execute')
      ->willThrowException(new PDOException());
    $stmt->method('fetch')
      ->willReturn(null);
    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willReturn($stmt);
    $this->db
      ->method('query')
      ->willReturn($stmt);
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);
    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();

    $this->expectException(DatabaseException::class);

    $this->mapper->deleteAll($userId);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testDeleteAllNotFoundSuccess() {
    $this->insertFixtures([ Fixtures\Users::class, Fixtures\Tracks::class, Fixtures\Positions::class ]);

    $userId = 1;

    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Position::class);
    $this->mapper = $this->getMockBuilder(Mapper\Position::class)
      ->setConstructorArgs([$this->db])
      ->onlyMethods([ 'removeFromFilesystem' ])
      ->getMock();
    $this->mapper
      ->expects($this->once())
      ->method('removeFromFilesystem')
      ->willThrowException(new NotFoundException());

    $this->mapper->deleteAll($userId);

    $this->assertTableRowCount(1, 'positions');
    $this->assertTableRowExists('positions', 3);
  }

  /**
   * @param array|null $expected
   * @param Entity\Position $actualPosition
   * @return void
   * @throws \Exception
   */
  private function assertPositionEquals(?array $expected, Entity\Position $actualPosition): void {
    $this->assertEquals($expected['id'], $actualPosition->id);
    $this->assertEquals($expected['user_id'], $actualPosition->userId);
    $this->assertEquals($expected['track_id'], $actualPosition->trackId);
    $date = new DateTimeImmutable($expected['time'], new DateTimeZone('UTC'));
    $this->assertEquals($date->getTimestamp(), $actualPosition->timestamp);
    $this->assertEquals($expected['longitude'], $actualPosition->longitude);
    $this->assertEquals($expected['latitude'], $actualPosition->latitude);
    $this->assertEquals($expected['altitude'], $actualPosition->altitude);
    $this->assertEquals($expected['speed'], $actualPosition->speed);
    $this->assertEquals($expected['bearing'], $actualPosition->bearing);
    $this->assertEquals($expected['accuracy'], $actualPosition->accuracy);
    $this->assertEquals($expected['provider'], $actualPosition->provider);
    $this->assertEquals($expected['comment'], $actualPosition->comment);
    $this->assertEquals($expected['image'], $actualPosition->image);
  }
}
