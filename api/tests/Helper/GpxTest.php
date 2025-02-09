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
use SimpleXMLElement;
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

    $trackName = 'Test track';
    $trackComment = 'Test comment';
    $trackId = 42;
    $latitude = 10.15;
    $longitude = -12.77;
    $elevation = 58.3;
    $time = '2025-01-01T10:56:33Z';
    $description = '2025-01-01T10:56:33Z';

    $gpxContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" 
     xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd 
     https://github.com/bfabiszewski/ulogger-android/1 https://raw.githubusercontent.com/bfabiszewski/ulogger-server/master/scripts/gpx_extensions1.xsd" 
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1" 
     creator="μlogger-server 1.2" version="1.1">
 <metadata>
  <name>$trackComment</name>
  <time>$time</time>
 </metadata>
 <trk>
  <name>$trackName</name>
  <trkseg>
   <trkpt lat="$latitude" lon="$longitude">
    <ele>$elevation</ele>
    <time>$time</time>
    <name>1</name>
    <desc><![CDATA[$description]]></desc>
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
      ->with($this->callback(function ($track) use ($trackId) {
        $track->id = $trackId;
        return true;
      }));

    // Expect mapperPosition->create() to be called once for the single track point.
    $this->positionMapperMock->expects($this->once())
      ->method('create')
      ->with($this->callback(function(Position $position) use ($latitude, $longitude, $time, $elevation, $description) {
        return $position->latitude === $latitude &&
          $position->longitude === $longitude &&
          $position->timestamp === strtotime($time) &&
          $position->altitude === $elevation &&
          $position->comment === $description;
      }));

    $gpxImporter = new Gpx('Default Track Name', $this->configMock, $this->mapperFactoryMock);
    $tracks = $gpxImporter->import($userId, $tempFile);

    // Clean up temporary file.
    unlink($tempFile);

    $this->assertIsArray($tracks);
    $this->assertCount(1, $tracks);
    $track = $tracks[0];
    $this->assertEquals($trackName, $track->name);
    $this->assertEquals($trackComment, $track->comment);
    $this->assertEquals($trackId, $track->id);
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
    $altitude = 12.34;
    $comment = 'Test comment';

    $position = new Position($timestamp, 100, $trackId, $latitude, $longitude);
    $position->altitude = $altitude;
    $position->comment = $comment;

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
    $this->assertEquals((string) $altitude, (string) $xml->trk->trkseg->trkpt->ele);
    $this->assertEquals($comment, (string) $xml->trk->trkseg->trkpt->desc);
    // name is point index
    $this->assertEquals('1', (string) $xml->trk->trkseg->trkpt->name);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws GpxParseException
   */
  public function testImportWithUloggerExtensions(): void {

    $trackName = 'Extended Track';
    $trackComment = 'Test Metadata';
    $trackId = 42;
    $speed = 7.5;
    $bearing = 45.0;
    $accuracy = 10;
    $provider = 'gps';
    $comment = 'Test description';

    $gpxContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" 
     xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd 
     https://github.com/bfabiszewski/ulogger-android/1 https://raw.githubusercontent.com/bfabiszewski/ulogger-server/master/scripts/gpx_extensions1.xsd" 
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1" 
     creator="μlogger-server 1.2" version="1.1">
  <metadata>
    <name>$trackComment</name>
  </metadata>
  <trk>
    <name>$trackName</name>
    <trkseg>
      <trkpt lat="12.34" lon="56.78">
        <time>2023-01-01T12:00:00Z</time>
        <desc>$comment</desc>
        <extensions>
          <ulogger:speed xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1">$speed</ulogger:speed>
          <ulogger:bearing xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1">$bearing</ulogger:bearing>
          <ulogger:accuracy xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1">$accuracy</ulogger:accuracy>
          <ulogger:provider xmlns:ulogger="https://github.com/bfabiszewski/ulogger-android/1">$provider</ulogger:provider>
        </extensions>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
    // Write GPX content to a temporary file.
    $tempFile = tempnam(sys_get_temp_dir(), 'gpx_');
    file_put_contents($tempFile, $gpxContent);
    $userId = 100;

    $this->trackMapperMock->expects($this->once())
      ->method('create')
      ->willReturnCallback(function($track) use ($trackId) {
        $track->id = $trackId;
      });

    $this->positionMapperMock->expects($this->once())
      ->method('create')
      ->with($this->callback(function(Position $position) use ($speed, $bearing, $accuracy, $provider, $comment) {
        return abs($position->speed - $speed) < 0.0001 &&
          abs($position->bearing - $bearing) < 0.0001 &&
          $position->accuracy === $accuracy &&
          $position->provider === $provider &&
          $position->comment === $comment;
      }));

    $gpxImporter = new Gpx('Default Track Name', $this->configMock, $this->mapperFactoryMock);
    $tracks = $gpxImporter->import($userId, $tempFile);

    // Cleanup temporary file.
    unlink($tempFile);

    // Assert that a track was imported.
    $this->assertIsArray($tracks);
    $this->assertCount(1, $tracks);
    $track = $tracks[0];
    $this->assertEquals($trackName, $track->name);
    $this->assertEquals($trackComment, $track->comment);
    $this->assertEquals($trackId, $track->id);
  }

  /**
   * @throws ServerException
   */
  public function testExportIncludesUloggerExtensions(): void {

    $speed = 7.5;
    $bearing = 45.0;
    $accuracy = 10;
    $provider = 'gps';

    $position = new Position(strtotime('2023-01-01T12:00:00Z'), 1, 1, 12.34, 56.78);
    $position->speed = $speed;
    $position->bearing = $bearing;
    $position->accuracy = $accuracy;
    $position->provider = $provider;
    $positions = [ $position ];

    $gpx = new Gpx('Test GPX', $this->configMock, $this->mapperFactoryMock);
    $file = $gpx->export($positions);

    $this->assertInstanceOf(File::class, $file);
    $this->assertSame('track1.gpx', $file->getFileName());
    $this->assertSame(Response::TYPE_GPX, $file->getMimeType());

    $xml = simplexml_load_string($file->getContent());
    $this->assertInstanceOf(SimpleXMLElement::class, $xml);

    $xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');
    $xml->registerXPathNamespace('ulogger', 'https://github.com/bfabiszewski/ulogger-android/1');

    // Locate the track point (<trkpt>) element.
    $trkpts = $xml->xpath('//gpx:trkpt');
    $this->assertNotEmpty($trkpts, 'Expected at least one <trkpt> element in the exported GPX.');
    $trkpt = $trkpts[0];

    $trkpt->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');
    $trkpt->registerXPathNamespace('ulogger', 'https://github.com/bfabiszewski/ulogger-android/1');

    $extensions = $trkpt->xpath('gpx:extensions');
    $this->assertNotEmpty($extensions, 'Expected a <extensions> element in the <trkpt>.');

    $speedNodes = $trkpt->xpath('gpx:extensions/ulogger:speed');
    $this->assertNotEmpty($speedNodes, 'Expected <ulogger:speed> element.');
    $this->assertSame($speed, (float) $speedNodes[0]);

    $bearingNodes = $trkpt->xpath('gpx:extensions/ulogger:bearing');
    $this->assertNotEmpty($bearingNodes, 'Expected <ulogger:bearing> element.');
    $this->assertEquals($bearing, (float) $bearingNodes[0]);

    $accuracyNodes = $trkpt->xpath('gpx:extensions/ulogger:accuracy');
    $this->assertNotEmpty($accuracyNodes, 'Expected <ulogger:accuracy> element.');
    $this->assertSame($accuracy, (int) $accuracyNodes[0]);

    $providerNodes = $trkpt->xpath('gpx:extensions/ulogger:provider');
    $this->assertNotEmpty($providerNodes, 'Expected <ulogger:provider> element.');
    $this->assertSame($provider, (string) $providerNodes[0]);
  }

}
