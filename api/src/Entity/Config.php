<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Entity;

use uLogger\Attribute\Column;
use uLogger\Attribute\JsonField;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Reflection;
use uLogger\Helper\Utils;
use uLogger\Mapper;
use uLogger\Mapper\MapperFactory;

/**
 * Handles config values
 */
class Config extends AbstractEntity {

  /** @var string Version number */
  public string $version = '2.0-beta';

  /** @var string Default map drawing framework */
  #[Column(name: 'map_api')]
  #[JsonField]
  public string $mapApi = 'openlayers';

  /** @var string|null Google Maps key */
  #[Column(name: 'google_key')]
  #[JsonField]
  public ?string $googleKey = null;

  /** @var Layer[] OpenLayers extra map layers */
  #[JsonField]
  public array $olLayers = [];

  /** @var float Default latitude for initial map */
  #[Column(name: 'latitude')]
  #[JsonField]
  public float $initLatitude = 52.23;

  /** @var float Default longitude for initial map */
  #[Column(name: 'longitude')]
  #[JsonField]
  public float $initLongitude = 21.01;

  /** @var bool Require login/password authentication */
  #[Column(name: 'require_auth')]
  #[JsonField]
  public bool $requireAuthentication = true;

  /** @var bool All users tracks are visible to authenticated user */
  #[Column(name: 'public_tracks')]
  #[JsonField]
  public bool $publicTracks = false;

  /** @var int Minimum required length of user password */
  #[Column(name: 'pass_lenmin')]
  #[JsonField]
  public int $passLenMin = 10;

  /**
   * @var int Required strength of user password
   * 0 = no requirements,
   * 1 = require mixed case letters (lower and upper),
   * 2 = require mixed case and numbers
   * 3 = require mixed case, numbers and non-alphanumeric characters
   */
  #[Column(name: 'pass_strength')]
  #[JsonField]
  public int $passStrength = 2;

  /** @var int Default interval in seconds for live auto reload */
  #[Column(name: 'interval_seconds')]
  #[JsonField]
  public int $interval = 10;

  /** @var string Default language code */
  #[Column]
  #[JsonField]
  public string $lang = 'en';

  /** @var string Default units */
  #[Column]
  #[JsonField]
  public string $units = 'metric';

  /** @var int Stroke weight */
  #[Column(name: 'stroke_weight')]
  #[JsonField]
  public int $strokeWeight = 2;

  /** @var string Stroke color */
  #[Column(name: 'stroke_color')]
  #[JsonField]
  public string $strokeColor = '#ff0000';

  /** @var float Stroke opacity */
  #[Column(name: 'stroke_opacity')]
  #[JsonField]
  public float $strokeOpacity = 1.0;

  /** @var string Stroke color */
  #[Column(name: 'color_normal')]
  #[JsonField]
  public string $colorNormal = '#ffffff';

  /** @var string Stroke color */
  #[Column(name: 'color_start')]
  #[JsonField]
  public string $colorStart = '#55b500';

  /** @var string Stroke color */
  #[Column(name: 'color_stop')]
  #[JsonField]
  public string $colorStop = '#ff6a00';

  /** @var string Stroke color */
  #[Column(name: 'color_extra')]
  #[JsonField]
  public string $colorExtra = '#cccccc';

  /** @var string Stroke color */
  #[Column(name: 'color_hilite')]
  #[JsonField]
  public string $colorHilite = '#feff6a';

  /**
   * @var int Maximum size of uploaded files in bytes.
   * Will be adjusted to system maximum upload size
   */
  #[Column(name: 'upload_maxsize')]
  #[JsonField]
  public int $uploadMaxSize = 5242880;

  /**
   * @throws DatabaseException
   * @throws ServerException
   */
  public static function createFromMapper(MapperFactory $factory): Config {
    /** @var Mapper\Config $mapper */
    $mapper = $factory->getMapper(Mapper\Config::class);
    return $mapper->fetch();
  }

  /**
   * FIXME: is it needed?
   * Create offline config
   * Read some fields from cookies
   */
  public static function createFromCookies(): Config {
    $config = new self();
    if (isset($_COOKIE['ulogger_api'])) { $config->mapApi = $_COOKIE['ulogger_api']; }
    if (isset($_COOKIE['ulogger_lang'])) { $config->lang = $_COOKIE['ulogger_lang']; }
    if (isset($_COOKIE['ulogger_units'])) { $config->units = $_COOKIE['ulogger_units']; }
    if (isset($_COOKIE['ulogger_interval'])) { $config->interval = (int) $_COOKIE['ulogger_interval']; }
    return $config;
  }


  /**
   * Check if given password matches user's one
   *
   * @param string $password Password
   * @return bool True if matches, false otherwise
   */
  public function validPassStrength(string $password): bool {
    return preg_match($this->passRegex(), $password) === 1;
  }

  /**
   * Regex to test if password matches strength and length requirements.
   * Valid for both php and javascript
   * @return string
   */
  public function passRegex(): string {
    $regex = '';
    if ($this->passStrength > 0) {
      // lower and upper case
      $regex .= '(?=.*[a-z])(?=.*[A-Z])';
    }
    if ($this->passStrength > 1) {
      // digits
      $regex .= '(?=.*[0-9])';
    }
    if ($this->passStrength > 2) {
      // not latin, not digits
      $regex .= '(?=.*[^a-zA-Z0-9])';
    }
    if ($this->passLenMin > 0) {
      $regex .= '(?=.{' . $this->passLenMin . ',})';
    }
    if (empty($regex)) {
      $regex = '.*';
    }
    return '/' . $regex . '/';
  }

  /**
   * @throws ServerException
   */
  public function setFromConfig(Config $config): void {
    foreach (Reflection::propertyGenerator($this, JsonField::class) as $field => $property) {
      $this->{$field} = $config->{$field};
    }
  }

  /**
   * Adjust uploadMaxSize to system limits
   */
  public function setUploadLimit(): void {
    $limit = Utils::getSystemUploadLimit();
    if ($this->uploadMaxSize <= 0 || $this->uploadMaxSize > $limit) {
      $this->uploadMaxSize = $limit;
    }
  }

  #[\Override]
  public static function fromPayload(mixed $payload): static {
    $instance = parent::fromPayload($payload);
    /** @var array $layers Layers are still an array here */
    $layers = $instance->olLayers;
    $instance->olLayers = [];
    foreach ($layers as $layer) {
      $instance->olLayers[] = Layer::fromPayload($layer);
    }
    return $instance;
  }
}

?>
