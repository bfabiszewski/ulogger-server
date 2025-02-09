<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Helper;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use uLogger\Component\Response;
use uLogger\Entity\Config;
use uLogger\Entity\File;
use uLogger\Entity\Position;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\GpxParseException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Gpx;
use uLogger\Mapper\MapperFactory;
use uLogger\Mapper\Position as PositionMapper;
use uLogger\Mapper\Track as TrackMapper;

final class GpxTest extends TestCase {
  /** @var MapperFactory|MockObject */
  private MapperFactory|MockObject $mapperFactoryMock;
  /** @var TrackMapper|MockObject */
  private MockObject|TrackMapper $trackMapperMock;
  /** @var PositionMapper|MockObject */
  private PositionMapper|MockObject $positionMapperMock;

  private Config|MockObject $configMock;
  /**
   * @throws Exception
   */
  protected function setUp(): void {
    $this->configMock = $this->createMock(Config::class);
    $this->trackMapperMock = $this->createMock(TrackMapper::class);
    $this->positionMapperMock = $this->createMock(PositionMapper::class);

    $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
    $this->mapperFactoryMock->method('getMapper')
      ->willReturnMap([
        [ TrackMapper::class, $this->trackMapperMock ],
        [ PositionMapper::class, $this->positionMapperMock ],
      ]);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws GpxParseException
   */
  public function testImportSuccess(): void {
    // Create a temporary GPX file with valid content.
    $gpxContent = <<<XML
<gpx>
  <metadata>
    <name>Test Metadata</name>
  </metadata>
  <trk>
    <name>Test Track</name>
    <trkseg>
      <trkpt lat="12.34" lon="56.78">
        <time>2023-01-01T12:00:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
    $tempFile = tempnam(sys_get_temp_dir(), 'gpx_');
    file_put_contents($tempFile, $gpxContent);
    $userId = 100;

    // Expect mapperTrack->create() to be called once.
    $this->trackMapperMock->expects($this->once())
      ->method('create')
      ->with($this->callback(function ($track) {
        // Simulate creation by setting an ID.
        $track->id = 42;
        return true;
      }));

    // Expect mapperPosition->create() to be called once for the single track point.
    $this->positionMapperMock->expects($this->once())
      ->method('create')
      ->with($this->isInstanceOf(Position::class));

    $gpxImporter = new Gpx('Default Track Name', $this->configMock, $this->mapperFactoryMock);
    $tracks = $gpxImporter->import($userId, $tempFile);

    // Clean up temporary file.
    unlink($tempFile);

    $this->assertIsArray($tracks);
    $this->assertCount(1, $tracks);
    $track = $tracks[0];
    $this->assertEquals('Test Track', $track->name);
    $this->assertEquals('Test Metadata', $track->comment);
    $this->assertEquals(42, $track->id);
  }

  /**
   * @throws ServerException
   * @throws DatabaseException
   */
  public function testImportMissingLatLon(): void {
    // GPX with a track point missing the 'lat' attribute.
    $gpxContent = <<<XML
<gpx>
  <trk>
    <trkseg>
      <trkpt lon="56.78">
        <time>2023-01-01T12:00:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
    $tempFile = tempnam(sys_get_temp_dir(), 'gpx_');
    file_put_contents($tempFile, $gpxContent);
    $userId = 100;

    $gpxImporter = new Gpx('Default Track Name', $this->configMock, $this->mapperFactoryMock);
    $this->expectException(GpxParseException::class);
    $gpxImporter->import($userId, $tempFile);
    unlink($tempFile);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws GpxParseException
   */
  public function testImportNoPositions(): void {
    // GPX with a track that has an empty track segment (no trkpt elements).
    $gpxContent = <<<XML
<gpx>
  <trk>
    <name>Test Track</name>
    <trkseg>
    </trkseg>
  </trk>
</gpx>
XML;
    $tempFile = tempnam(sys_get_temp_dir(), 'gpx_');
    file_put_contents($tempFile, $gpxContent);
    $userId = 100;

    // Expect mapperTrack->create() to be called and assign an ID.
    $this->trackMapperMock->expects($this->once())
      ->method('create')
      ->willReturnCallback(function ($track) {
        $track->id = 42;
      });
    // Expect that no positions are created.
    $this->positionMapperMock->expects($this->never())->method('create');
    // Expect that delete() is called on the track since no positions were imported.
    $this->trackMapperMock->expects($this->once())->method('delete');

    $gpxImporter = new Gpx('Default Track Name', $this->configMock, $this->mapperFactoryMock);
    $tracks = $gpxImporter->import($userId, $tempFile);
    unlink($tempFile);

    // Since there are no positions, import() should return an empty array.
    $this->assertIsArray($tracks);
    $this->assertCount(0, $tracks);
  }

  /**
   * @throws ServerException
   */
  public function testExport(): void {
    $trackId = 42;
    $latitude = 12.34;
    $longitude = 56.78;
    $timestamp = 1;
    $position = new Position($timestamp, 100, $trackId, $latitude, $longitude);

    $positions = [ $position ];

    $trackName = 'Exported Track';
    $gpxExporter = new Gpx($trackName, $this->configMock, $this->mapperFactoryMock);
    $file = $gpxExporter->export($positions);

    $this->assertInstanceOf(File::class, $file);
    $this->assertSame("track$trackId.gpx", $file->getFileName());
    $this->assertSame(Response::TYPE_GPX, $file->getMimeType());

    // Parse the exported XML and verify structure.
    $xml = simplexml_load_string($file->getContent());
    $this->assertEquals('gpx', $xml->getName());
    $this->assertEquals($trackName, (string) $xml->metadata->name);
    // first position time
    $this->assertEquals(gmdate('Y-m-d\TH:i:s\Z', $timestamp), (string) $xml->metadata->time);
    $this->assertNotEmpty($xml->trk);
    // Ensure that at least one track point exists.
    $this->assertNotEmpty($xml->trk->trkseg->trkpt);
    $this->assertEquals((string) $latitude, (string) $xml->trk->trkseg->trkpt['lat']);
    $this->assertEquals((string) $longitude, (string) $xml->trk->trkseg->trkpt['lon']);
    $this->assertEquals(gmdate('Y-m-d\TH:i:s\Z', $timestamp), (string) $xml->trk->trkseg->trkpt->time);
    // name is point index
    $this->assertEquals('1', (string) $xml->trk->trkseg->trkpt->name);
  }
}
