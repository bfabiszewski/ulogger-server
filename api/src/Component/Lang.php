<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Component;

use uLogger\Entity\Config;
use uLogger\Helper\Utils;

/**
 * Localization
 */
class Lang {

  /**
   * Available languages
   *
   * @var array
   */
  private static array $languages = [
    'ca' => 'Català',
    'cs' => 'Čeština',
    'de' => 'Deutsch',
    'el' => 'Ελληνικά',
    'en' => 'English',
    'es' => 'Español',
    'eu' => 'Euskera',
    'fi' => 'Suomi',
    'fr' => 'Français',
    'gl' => 'Galego',
    'it' => 'Italiano',
    'pl' => 'Polski',
    'pt-br' => 'Português (Br)',
    'ru' => 'Русский',
    'sk' => 'Slovenčina',
    'zh-cn' => '简体中文'
  ];

  /**
   * Application strings
   * Array of key => translation pairs
   *
   * @var array
   */
  private array $strings;
  /**
   * Setup script strings
   * Array of key => translation pairs
   *
   * @var array
   */
  private array $setupStrings;

  /**
   * Constructor
   *
   * @param Config $config Config
   */
  public function __construct(Config $config) {
    $language = $config->lang;
    $lang = [];
    $langSetup = [];
    // always load en base
    require(Utils::getSourceDir() . '/Lang/en.php');

    // override with translated strings if needed
    // missing strings will be displayed in English
    if ($language !== 'en' && array_key_exists($language, self::$languages)) {
      require(Utils::getSourceDir() . "/Lang/$language.php");
    }

    $this->strings = $lang;
    $this->setupStrings = $langSetup;
  }

  /**
   * Get supported languages array
   * Language code => Native language name
   *
   * @return array
   */
  public static function getLanguages(): array {
    return self::$languages;
  }

  /**
   * Get translated strings array
   * Key => translation string
   *
   * @return array
   */
  public function getStrings(): array {
    return $this->strings;
  }

  /**
   * Get translated strings array for setup script
   * Key => translation string
   *
   * @return array
   */
  public function getSetupStrings(): array {
    return $this->setupStrings;
  }

}

?>
