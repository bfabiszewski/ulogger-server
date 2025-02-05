<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Entity;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use uLogger\Entity\Config;
use uLogger\Entity\Layer;
use uLogger\Exception\InvalidInputException;
use uLogger\Mapper\MapperFactory;
use uLogger\Exception\ServerException;
use uLogger\Exception\DatabaseException;

class ConfigTest extends TestCase {

  public function testDefaultValues(): void {
    $config = new Config();

    $expectedDefaults = [
      'mapApi' => 'openlayers',
      'googleKey' => null,
      'olLayers' => [],
      'initLatitude' => 52.23,
      'initLongitude' => 21.01,
      'requireAuthentication' => true,
      'publicTracks' => false,
      'passLenMin' => 10,
      'passStrength' => 2,
      'interval' => 10,
      'lang' => 'en',
      'units' => 'metric',
      'strokeWeight' => 2,
      'strokeColor' => '#ff0000',
      'strokeOpacity' => 1.0,
      'uploadMaxSize' => 5242880,
    ];

    foreach ($expectedDefaults as $property => $expectedValue) {
      $this->assertSame($expectedValue, $config->{$property});
    }
  }

  public function testValidPassStrength3(): void {
    $config = new Config();

    $config->passLenMin = 0;
    $config->passStrength = 3;

    $this->assertFalse($config->validPassStrength('weakpass'));
    $this->assertFalse($config->validPassStrength('weakpass1'));
    $this->assertFalse($config->validPassStrength('WeakPass1'));
    $this->assertTrue($config->validPassStrength('StrongPass1!'));
  }

  public function testValidPassStrength2(): void {
    $config = new Config();

    $config->passLenMin = 0;
    $config->passStrength = 2;

    $this->assertFalse($config->validPassStrength('weakpass'));
    $this->assertFalse($config->validPassStrength('weakPass'));
    $this->assertTrue($config->validPassStrength('WeakPass1'));
    $this->assertTrue($config->validPassStrength('StrongPass1!'));
  }

  public function testValidPassStrength1(): void {
    $config = new Config();

    $config->passLenMin = 0;
    $config->passStrength = 1;

    $this->assertFalse($config->validPassStrength('weakpass'));
    $this->assertTrue($config->validPassStrength('weakPass'));
    $this->assertTrue($config->validPassStrength('WeakPass1'));
    $this->assertTrue($config->validPassStrength('StrongPass1!'));
  }

  public function testValidPassStrength0(): void {
    $config = new Config();

    $config->passLenMin = 0;
    $config->passStrength = 0;

    $this->assertTrue($config->validPassStrength('weakpass'));
    $this->assertTrue($config->validPassStrength('weakPass'));
    $this->assertTrue($config->validPassStrength('WeakPass1'));
    $this->assertTrue($config->validPassStrength('StrongPass1!'));
  }

  public function testValidPassMinLength(): void {
    $config = new Config();

    $config->passLenMin = 8;
    $config->passStrength = 0;

    $this->assertTrue($config->validPassStrength('12345678'));
    $this->assertFalse($config->validPassStrength('1234567'));
  }

  public function testCreateFromCookies(): void {
    $mapApi = 'openlayers';
    $lang = 'fr';
    $units = 'imperial';
    $interval = 30;

    $_COOKIE['ulogger_api'] = $mapApi;
    $_COOKIE['ulogger_lang'] = $lang;
    $_COOKIE['ulogger_units'] = $units;
    $_COOKIE['ulogger_interval'] = (string) $interval;

    $config = Config::createFromCookies();

    $this->assertSame($mapApi, $config->mapApi);
    $this->assertSame($lang, $config->lang);
    $this->assertSame($units, $config->units);
    $this->assertSame(30, $config->interval);
  }

  /**
   * @throws DatabaseException
   * @throws ServerException
   * @throws Exception
   */
  public function testCreateFromMapper(): void {
    $mockMapper = $this->createMock(\uLogger\Mapper\Config::class);
    $mockConfig = new Config();
    $mockMapper->method('fetch')->willReturn($mockConfig);

    $mockMapperFactory = $this->createMock(MapperFactory::class);
    $mockMapperFactory
      ->method('getMapper')
      ->willReturn($mockMapper);

    $config = Config::createFromMapper($mockMapperFactory);
    $this->assertSame($mockConfig, $config);
  }

  /**
   * @throws ServerException
   * @throws InvalidInputException
   */
  public function testFromPayload(): void {

    $payload = [
      'mapApi' => 'openlayers',
      'googleKey' => 'test-key',
      'olLayers' => [['id' => 1, 'name' => 'Layer 1', 'url' => 'http://example.com', 'priority' => 5]],
      'initLatitude' => 40.7128,
      'initLongitude' => -74.0060,
      'requireAuthentication' => false,
      'publicTracks' => true,
      'passLenMin' => 12,
      'passStrength' => 3,
      'interval' => 15,
      'lang' => 'fr',
      'units' => 'imperial',
      'strokeWeight' => 3,
      'strokeColor' => '#00ff00',
      'strokeOpacity' => 0.5,
      'uploadMaxSize' => 10485760
    ];

    $config = Config::fromPayload($payload);

    foreach ($payload as $property => $expectedValue) {
      if ($property === 'olLayers') {
        $this->assertCount(1, $config->olLayers);
        $this->assertInstanceOf(Layer::class, $config->olLayers[0]);
        $this->assertSame($expectedValue[0]['id'], $config->olLayers[0]->id);
        $this->assertSame($expectedValue[0]['name'], $config->olLayers[0]->name);
        $this->assertSame($expectedValue[0]['url'], $config->olLayers[0]->url);
        $this->assertSame($expectedValue[0]['priority'], $config->olLayers[0]->priority);
      } else {
        $this->assertSame($expectedValue, $config->{$property});
      }
    }
  }

  /**
   * @throws ServerException
   */
  public function testSetFromConfig(): void {

    $mapApi = 'openlayers';
    $lang = 'es';
    $interval = 20;
    $strokeColor = '#123456';

    $sourceConfig = new Config();
    $sourceConfig->mapApi = $mapApi;
    $sourceConfig->lang = $lang;
    $sourceConfig->interval = $interval;
    $sourceConfig->strokeColor = $strokeColor;

    $targetConfig = new Config();
    $targetConfig->setFromConfig($sourceConfig);

    $this->assertSame($mapApi, $targetConfig->mapApi);
    $this->assertSame($lang, $targetConfig->lang);
    $this->assertSame($interval, $targetConfig->interval);
    $this->assertSame($strokeColor, $targetConfig->strokeColor);
  }
}
