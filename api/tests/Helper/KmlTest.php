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
use uLogger\Helper\Kml;

final class KmlTest extends TestCase {

  private Config|MockObject $configMock;

  /**
   * @throws Exception
   */
  protected function setUp(): void {
    $this->configMock = $this->createMock(Config::class);
  }

  public function testExportDefaultUnits(): void {
    // Use metric units (default).
    $this->configMock->units = 'metric';
    // Create two dummy positions.
    $pos1 = new Position(strtotime('2023-01-01T12:00:00Z'), 1, 42, 12.345, 67.89);
    $pos1->userName = 'testUser';
    $pos1->trackName = 'testTrack';
    $pos2 = new Position(strtotime('2023-01-01T12:00:10Z'), 1, 42, 12.346, 67.891);
    $pos2->userName = 'testUser';
    $pos2->trackName = 'testTrack';
    $positions = [ $pos1, $pos2 ];

    $kml = new Kml('Test KML', $this->configMock);
    $file = $kml->export($positions);

    $this->assertInstanceOf(File::class, $file);
    $this->assertSame('track42.kml', $file->getFileName());
    $this->assertSame(Response::TYPE_KML, $file->getMimeType());

    // Parse the exported XML.
    $xml = simplexml_load_string($file->getContent());
    $this->assertEquals('kml', $xml->getName(), 'Root element should be <kml>');
    $this->assertEquals('Test KML', (string) $xml->Document->name, 'Document name should match');

    // Check that there is at least one Placemark element.
    $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
    $placeMarks = $xml->xpath('//kml:Placemark');
    $this->assertNotEmpty($placeMarks, 'Expected at least one <Placemark> element');
    // Verify description contains the metric unit "km/h".
    $this->assertStringContainsString('km/h', (string) $placeMarks[0]->description, "Expected at least one description to contain 'km/h'" );

    // Check that there is a LineString element.
    $lineString = $xml->xpath('//kml:LineString');
    $this->assertNotEmpty($lineString, 'Expected a <LineString> element');
  }

  public function testExportImperialUnits(): void {
    // Use imperial units.
    $this->configMock->units = 'imperial';
    // Create two dummy positions with speed and altitude.
    $longitude = 67.89;
    $latitude = 12.345;
    $pos1 = new Position(strtotime('2023-01-01T12:00:00Z'), 1, 42, $latitude, $longitude);
    $pos1->userName = 'testUser';
    $pos1->trackName = 'testTrack';
    $pos2 = new Position(strtotime('2023-01-01T12:00:10Z'), 1, 42, 12.346, 67.891);
    $pos2->userName = 'testUser';
    $pos2->trackName = 'testTrack';
    $positions = [ $pos1, $pos2 ];

    $kml = new Kml('Imperial KML', $this->configMock);
    $file = $kml->export($positions);

    $this->assertInstanceOf(File::class, $file);
    $this->assertSame('track42.kml', $file->getFileName());
    $this->assertSame(Response::TYPE_KML, $file->getMimeType());

    // Parse the exported XML.
    $xml = simplexml_load_string($file->getContent());
    // Check that descriptions use imperial units: look for "mph" for speed.
    $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
    $placeMarks = $xml->xpath('//kml:Placemark');
    $this->assertStringContainsString('mph', (string) $placeMarks[0]->description, "Expected at least one description to contain 'mph'" );
  }

  public function testExportStructure(): void {
    $this->configMock->units = 'imperial';
    // Create three dummy positions.
    $longitude = 67.89;
    $latitude = 12.345;
    $pos1 = new Position(strtotime('2023-01-01T12:00:00Z'), 1, 42, $latitude, $longitude);
    $pos1->userName = 'testUser';
    $pos1->trackName = 'testTrack';
    $pos2 = new Position(strtotime('2023-01-01T12:00:10Z'), 1, 42, 12.346, 67.891);
    $pos2->userName = 'testUser';
    $pos2->trackName = 'testTrack';
    $pos3 = new Position(strtotime('2023-01-01T12:00:20Z'), 1, 42, 12.347, 67.892);
    $pos3->userName = 'testUser';
    $pos3->trackName = 'testTrack';
    $positions = [ $pos1, $pos2, $pos3 ];

    $kml = new Kml('Structured KML', $this->configMock);
    $file = $kml->export($positions);
    $xml = simplexml_load_string($file->getContent());
    $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

    // Check root and Document element.
    $this->assertEquals('kml', $xml->getName(), 'Root element should be <kml>');
    $this->assertNotEmpty($xml->Document, 'Missing <Document> element');

    // Check that style definitions exist (e.g. lineStyle, redStyle, greenStyle, grayStyle).
    $styles = $xml->xpath('//kml:Style');
    $this->assertNotEmpty($styles, 'No <Style> elements found');

    // Verify that there is a Placemark with a LineString.
    $linePlacemark = $xml->xpath('//kml:Placemark[kml:LineString]');
    $this->assertNotEmpty($linePlacemark, 'Missing <Placemark> with a <LineString>');

    // Check that each Placemark for a point has a description and a Point element.
    $pointPlaceMarks = $xml->xpath('//kml:Placemark[kml:Point]');
    $this->assertNotEmpty($pointPlaceMarks, 'Missing <Placemark> elements with <Point>');
    foreach ($pointPlaceMarks as $placemark) {
      $this->assertNotEmpty($placemark->description, 'A <Placemark> is missing a <description>');
      $this->assertNotEmpty($placemark->Point, 'A <Placemark> is missing a <Point> element');
    }
    // Check point coordinates
    $this->assertSame("$longitude,$latitude", (string) $pointPlaceMarks[0]->Point->coordinates, 'Point coordinates should match' );
  }
}
