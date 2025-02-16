<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper;

use PDOException;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use uLogger\Component\Db;
use uLogger\Entity\Config;
use uLogger\Entity\Layer;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\ServerException;
use uLogger\Mapper;
use uLogger\Mapper\MapperFactory;

class ConfigTest extends AbstractMapperTestCase {

  protected Mapper\Config $mapper;

  /**
   * @throws ServerException
   * @throws DatabaseException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->mapper = $this->mapperFactory->getMapper(Mapper\Config::class);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   */
  public function testFetchSuccess() {
    $this->insertFixtures([ Fixtures\Config::class, Fixtures\OlLayers::class ]);

    $config = $this->mapper->fetch();

    $this->assertStringStartsWith('test', $config->colorStart);
    $this->assertEquals('test_key', $config->googleKey);
    $this->assertEquals(1234, $config->uploadMaxSize);
    $this->assertEquals(21.1, $config->initLongitude);
    $this->assertEquals(53.1, $config->initLatitude);
    $this->assertEquals([ new Layer(1, 'layer1', 'https://testUrl', 0) ], $config->olLayers);
  }

  /**
   * @throws ServerException
   * @throws Exception
   */
  public function testFetchException() {
    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('query')
      ->willThrowException(new PDOException());
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Config::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->fetch();
  }

  /**
   * @throws DatabaseException
   */
  public function testUpdateSuccess() {
    $this->insertFixtures([ Fixtures\Config::class, Fixtures\OlLayers::class ]);

    $rowCount = count((new Fixtures\Config())->records);
    $config = new Config();
    $config->colorStart = '#1234';
    $config->googleKey = 'new_key';
    $config->uploadMaxSize = 2048;
    $config->initLatitude = 1.0;
    $config->initLongitude = 1.2;
    $config->olLayers = [ new Layer(2, 'new', 'https://newUrl', 7) ];

    $this->assertTableRowCount($rowCount, 'config');

    $this->mapper->update($config);

    $configRows = $this->getTableAllRows('config');
    $layersRows = $this->getTableAllRows('ol_layers');

    $this->assertTableRowCount($rowCount, 'config');
    $this->assertEquals($config->colorStart, $this->unserialize($this->getArrayRowByKey($configRows, 'name', 'color_start')['value']));
    $this->assertEquals($config->googleKey, $this->unserialize($this->getArrayRowByKey($configRows, 'name', 'google_key')['value']));
    $this->assertEquals($config->uploadMaxSize, $this->unserialize($this->getArrayRowByKey($configRows, 'name', 'upload_maxsize')['value']));
    $this->assertEquals($config->initLatitude, $this->unserialize($this->getArrayRowByKey($configRows, 'name', 'latitude')['value']));
    $this->assertEquals($config->initLongitude, $this->unserialize($this->getArrayRowByKey($configRows, 'name', 'longitude')['value']));
    $this->assertEquals(count($config->olLayers), count($layersRows));
    $this->assertEquals($config->olLayers[0]->id, $layersRows[0]['id']);
    $this->assertEquals($config->olLayers[0]->name, $layersRows[0]['name']);
    $this->assertEquals($config->olLayers[0]->url, $layersRows[0]['url']);
    $this->assertEquals($config->olLayers[0]->priority, $layersRows[0]['priority']);
  }

  /**
   * @throws ServerException
   * @throws Exception
   */
  public function testUpdateException() {
    $stmt = $this->createMock(PDOStatement::class);
    $stmt->expects($this->once())
      ->method('execute')
      ->willThrowException(new PDOException());
    $this->db = $this->createMock(Db::class);
    $this->db->expects($this->once())
      ->method('prepare')
      ->willReturn($stmt);
    $this->mapperFactory = new MapperFactory($this->db);
    $this->mapper = $this->mapperFactory->getMapper(Mapper\Config::class);

    $this->expectException(DatabaseException::class);

    $this->mapper->update($this->createMock(Config::class));
  }
}
